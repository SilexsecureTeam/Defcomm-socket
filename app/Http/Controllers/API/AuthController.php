<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use App\Mail\Invitation;
use App\Models\Language;
use App\Models\CompanyUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\PasswordResetMail;
use App\Models\StatementAgreement;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function smsSent($phone = '08188822411', $otp)
    {
        $phone = '08188822411';
        $headers = [
            'Content-Type' => 'application/json',
            'accept',
            '*/*',
            'Authorization' => 'Bearer sendchamp_live_$2a$10$1GX8wG0X7gWVtBgmQU3h0uDUUR6uA2z6iOKrBGv1ICCj3nZ3NdrYe',
        ];

        $body = [
            'message' => 'Welcome to Defcomm!, Your OTP is ' . $otp . ' For assistance, contact our customer care at +2348020989037.',
            'to' => ['234' . $phone],
            'sender_name' => 'Sendchamp',
            'route' => 'dnd'
        ];
        // '{"message":"hello","to":["2347062787760"],"sender_name":"Sendchamp","route":"dnd"}',

        Http::withHeaders($headers)->post('https://api.sendchamp.com/api/v1/sms/send', $body);
        return true;
    }

    public function emailVerify(Request $request)
    {
        $user = User::where('email', '=', $request->input('email'))->first();

        if($user){
            return response()->json(['status' => '400', 'message' => 'This email is already used', 'data' => []], 401);
        }else{
            return response()->json(['status' => '200','message' => 'This email is not yet taken', 'data' => []], 201);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Error Occur', 'data' => $validator->messages()], 401);
        }

        $otp = rand(1000, 9999);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'role' => $request->role == "company" ? 'admin' : 'user',
            'password' => Hash::make($request->password),
            'access_token' => uniqid(),
            'otp' => $otp
        ]);

        if($request->role == "company"){
            $comp = CompanyUser::create([
                'name' => $request->nameorg,
                'user_id' => $user->id
            ]);

            $user->update(['company_id' => $comp->id]);
        }
        if($request->verify_option == "sms"){

            $bodysms = 'Welcome to Defcomm!, Your OTP is ' . $otp . ' or use https://cloud.defcomm.ng/onboarding to join.';

            $this->TermiiSms($request->phone, $bodysms);
        }else{
            Mail::to($request->email)->send(new Invitation($request->name, $request->email, encrypt($request->email), $otp));
        }
        
        return response()->json(['message' => 'User registered successfully!', 'data' => $user], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // $user = User::where('phone', '=', $request->input('emailOrPhone'))->orWhere('email', $request->input('emailOrPhone'));

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            // throw ValidationException::withMessages([
            //     'email' => ['The provided credentials are incorrect.'],
            // ]);
            return response()->json([
                'message' => 'Wrong login detail',
                'data' => [],
            ], 401);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        if ($request->device_token) {
            $user->update(['device_token' => $request->device_token]);
        }

        if ($request->device_type) {
            $user->update(['device_type' => $request->device_type]);
        }

        return response()->json([
            'message' => 'Login successfully',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user_enid' => encrypt($user->id),
                'user' => $user
            ],
        ], 201);
    }

    public function requestOtpSms(Request $request)
    {
        $users = User::where('phone', '=', $request->input('phone'))->orWhere('phone', '=', '+234' . $request->input('phone'))->orWhere('phone', '=', '234' . $request->input('phone'))->orWhere('phone', '=', preg_replace('/^\+?234/', '', $request->input('phone')))->orWhere('phone', '=', preg_replace('/^\+?+234/', '', $request->input('phone')));

        if ($users->first()) {
            $user = $users->first();
            $otp = rand(1000, 9999);
            $user->update(['otp' => $otp]);
            $this->smsSent($request->get('phone'), $otp);
            Mail::to($user->email)->send(new OtpMail($user->name, $otp));

            $bodysms = 'Welcome to Defcomm!, Your OTP is ' . $otp . ' or use https://cloud.defcomm.ng/onboarding to join.';

            $this->TermiiSms($request->phone, $bodysms);
            return response()->json(['status' => 200, 'message' => 'OTP has been sent', 'otp' => $otp], 200);
        } else {
            return response()->json(['status' => 400, 'error' => "This user does not exist."], 400);
        }
    }

    public function loginWithPhone(Request $request)
    {
        $user = User::where('phone', '=', $request->input('phone'))->where('otp', $request->otp);
        if ($user->get()->isNotEmpty()) {
            $cur = Carbon::now()->subMinute(2)->format('Y-m-d H:i:s');
            if (strtotime($user->first()->updated_at->format('Y-m-d H:i:s')) >= strtotime($cur)) {

                $token = $user->first()->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'status' => 200,
                    'message' => 'Login successfully',
                    'data' => [
                        'access_token' => $token,
                        'token_type' => 'Bearer',
                        'user_enid' => encrypt($user->first()->id),
                        'user' => $user->first()
                    ],
                ], 201);
            } else {
                return response()->json(['status' => 400, 'error' => "The OTP has expired. Request again"], 401);
            }
        } else {
            return response()->json(['status' => 400, 'error' => "Invalid input. Try again"], 401);
        }
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Str::random(64);
            $user->password_reset_token = $token;
            $user->save();

            // Send email with password reset link
            // This is a simplified example
            Mail::send('emails.reset_password', ['token' => $token], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Password Notification');
            });
        }

        return response()->json(['message' => 'If your email is registered, you will receive a password reset link'], 201);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->where('password_reset_token', $request->token)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid token or email'], 401);
        }

        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->save();

        event(new PasswordReset($user));

        return response()->json(['message' => 'Password has been successfully reset'], 201);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link'], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 201);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(['message' => 'Email has been verified'], 201);
    }

    public function appAuthenticate(Request $request)
    {
        $user = User::where('phone', $request->phone)->where('email', $request->email)->where('access_token', $request->access_token)->first();

        if ($user) {
            $user->update(['device' => 'yes']);
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'Login successfully',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user_enid' => encrypt($user->id),
                    'user' => $user
                ],
            ], 201);
        }

        return response()->json([
            'message' => 'Wrong login detail',
            'data' => [],
        ], 401);
    }

    public function appresetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::find(auth()->user()->id);

        $user->password = Hash::make($request->password);
        $user->save();

        Mail::to($user->email)->send(new PasswordResetMail($user->name));

        return response()->json(['message' => 'Password has been successfully reset'], 201);
    }

    public function appConfiguration(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if ($request->has('signal_blocking')) {
            $user->update([
                'signal_blocking' => $request->signal_blocking,
            ]);
        }

        if ($request->has('remote_management')) {
            $user->update([
                'remote_management' => $request->remote_management,
            ]);
        }

        if ($request->has('encrypted_storage')) {
            $user->update([
                'encrypted_storage' => $request->encrypted_storage,
            ]);
        }

        if ($request->has('self_wipe')) {
            $user->update([
                'self_wipe' => $request->self_wipe,
            ]);
        }

        if ($request->has('imei')) {
            $user->update([
                'imei' => $request->imei,
            ]);
        }

        if ($request->has('device_type')) {
            $user->update([
                'device_type' => $request->device_type,
            ]);
        }

        return response()->json(['message' => 'Configuration has been successfully reset'], 201);
    }

    public function appDevelopermode(Request $request)
    {
        $user = User::find(auth()->user()->id);

        if($request->rc_number){
            $user->update(['rc_number' => $request->rc_number]);
        }

        if ($request->hasFile('rc_doc')) {
            $rc_doc = $request->file('rc_doc');
            $rc_doc_name = time() . '_rc_doc.' . $rc_doc->getClientOriginalExtension();
            $rc_doc->move(public_path('storeuser/rc_doc'), $rc_doc_name);

            // Delete the old file if it exists
            if ($user->rc_doc && file_exists(public_path($user->rc_doc))) {
                try {
                    unlink(public_path($user->rc_doc));
                } catch (\Exception $e) {
                    // Log the error if needed
                    // Log::error("Failed to delete old rc_doc: " . $e->getMessage());
                }
            }

            // Update the new file path in the database
            $user->update(['rc_doc' => 'storeuser/rc_doc/' . $rc_doc_name]);
        }

        if($request->tin){
            $user->update(['tin' => $request->tin]);
        }

        if ($request->hasFile('tin_doc')) {
            $tin_doc = $request->file('tin_doc');
            $tin_doc_name = time() . '_tin_doc.' . $tin_doc->getClientOriginalExtension();
            $tin_doc->move(public_path('storeuser/tin_doc'), $tin_doc_name);

            // Delete the old file if it exists
            if ($user->tin_doc && file_exists(public_path($user->tin_doc))) {
                try {
                    unlink(public_path($user->tin_doc));
                } catch (\Exception $e) {
                    // Log the error if needed
                    // Log::error("Failed to delete old tin_doc: " . $e->getMessage());
                }
            }

            // Update the new file path in the database
            $user->update(['tin_doc' => 'storeuser/tin_doc/' . $tin_doc_name]);
        }

        if($request->developer_display_name ){
            $user->update(['developer_display_name' => $request->developer_display_name ]);
        }

        if($request->website ){
            $user->update(['website' => $request->website ]);
        }

        if ($request->hasFile('selfie')) {
            $selfie = $request->file('selfie');
            $selfie_name = time() . '_selfie.' . $selfie->getClientOriginalExtension();
            $selfie->move(public_path('storeuser/selfie'), $selfie_name);

            // Delete the old file if it exists
            if ($user->selfie && file_exists(public_path($user->selfie))) {
                try {
                    unlink(public_path($user->selfie));
                } catch (\Exception $e) {
                    // Log the error if needed
                    // Log::error("Failed to delete old selfie: " . $e->getMessage());
                }
            }

            // Update the new file path in the database
            $user->update(['selfie' => 'storeuser/selfie/' . $selfie_name]);
        }

        if ($request->hasFile('id_card_front')) {
            $id_card_front = $request->file('id_card_front');
            $id_card_front_name = time() . '_id_card_front.' . $id_card_front->getClientOriginalExtension();
            $id_card_front->move(public_path('storeuser/id_card_front'), $id_card_front_name);

            // Delete the old file if it exists
            if ($user->id_card_front && file_exists(public_path($user->id_card_front))) {
                try {
                    unlink(public_path($user->id_card_front));
                } catch (\Exception $e) {
                    // Log the error if needed
                    // Log::error("Failed to delete old id_card_front: " . $e->getMessage());
                }
            }

            // Update the new file path in the database
            $user->update(['id_card_front' => 'storeuser/id_card_front/' . $id_card_front_name]);
        }

        if ($request->hasFile('id_card_back')) {
            $id_card_back = $request->file('id_card_back');
            $id_card_back_name = time() . '_id_card_back.' . $id_card_back->getClientOriginalExtension();
            $id_card_back->move(public_path('storeuser/id_card_back'), $id_card_back_name);

            // Delete the old file if it exists
            if ($user->id_card_back && file_exists(public_path($user->id_card_back))) {
                try {
                    unlink(public_path($user->id_card_back));
                } catch (\Exception $e) {
                    // Log the error if needed
                    // Log::error("Failed to delete old id_card_back: " . $e->getMessage());
                }
            }

            // Update the new file path in the database
            $user->update(['id_card_back' => 'storeuser/id_card_back/' . $id_card_back_name]);
        }

        $user->update([
            'app_role' => "developer",
            'statusApp' => "pending"
        ]);
        
        return response()->json(
            [
                'status' => '200',
                'message' => 'Document upload',
                'data' => $user
            ],
            201
        );
    }

    public function appLanguage()
    {
        $data = Language::where('status', 'active')->get();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Language List',
                'data' => $data
            ],
            201
        );
    }

    public function appAgreements($term = null)
    {
        $data = StatementAgreement::where('status', 'active')->get();

        return response()->json(
            [
                'status' => '200',
                'message' => 'Agreement List',
                'data' => $data
            ],
            201
        );
    }
}
