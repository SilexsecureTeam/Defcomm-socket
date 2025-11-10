<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\BountyUser;
use App\Mail\BountyUserOtp;
use Illuminate\Http\Request;
use App\Mail\BountyUserVerify;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class BountyController extends Controller
{
    // Register user
    public function register(Request $request)
    {
        try {
            $request->validate([
                'firstName' => 'required|string|max:100',
                'lastName'  => 'required|string|max:100',
                'username'  => 'required|string|max:100|unique:bounty_users,username',
                'email'     => 'required|email|max:150|unique:bounty_users,email',
                'phone'     => 'nullable|string|max:20|unique:bounty_users,phone',
                'password'  => 'required|string|min:6',
            ]);

            $otp = rand(1000, 9999);

            $user = BountyUser::create([
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'country' => $request->country,
                'user_type' => $request->user_type,
                'password' => Hash::make($request->password),
                'otp' => $otp
            ]);

            Mail::to($request->email)->send(new BountyUserVerify($request, $otp));

            return response()->json([
                'status' => '200',
                'message' => 'User created successfully',
                'data' => $user,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => '400',
                'message' => 'Validation failed.',
                'data' => $e->errors(), // full list of field errors
            ], 401);
        }
    }

    public function verify(Request $request)
    {
        $user = BountyUser::where('email', $request->userlogin)->orWhere('username', $request->userlogin)->first();

        if($user->otp != $request->otp){
            return response()->json([
                'status' => '400',
                'message' => 'Wrong OTP. Try again!',
                'data' => null, // full list of field errors
            ], 401);
        }

        if (now()->diffInSeconds($user->updated_at) > 60) {
            return response()->json([
                'status' => '400',
                'message' => 'Token expired. Please login again',
                'data' => null, // full list of field errors
            ], 401);
        }

        if ($user) {
            $user->update(['status' => 'active']);
            return response()->json([
                'status' => '200',
                'message' => 'Account successfully verified',
                'data' => null,
            ], 201);
        } else {
            return response()->json([
                'status' => '400',
                'message' => 'Account not found.',
                'data' => null, // full list of field errors
            ], 401);
        }
    }

    public function requestOtp(Request $request)
    {
        $user = BountyUser::where('phone', '=', $request->input('userlogin'))->orWhere('phone', '=', '+234' . $request->input('userlogin'))->orWhere('phone', '=', '234' . $request->input('userlogin'))->orWhere('phone', '=', preg_replace('/^\+?234/', '', $request->input('userlogin')))->orWhere('phone', '=', preg_replace('/^\+?+234/', '', $request->input('userlogin')))->orWhere('email', '=', $request->input('userlogin'))->orWhere('username', '=', $request->input('userlogin'))->first();

        if ($user) {
            $otp = rand(1000, 9999);
            $user->update(['otp' => $otp]);
            // $this->smsSent($request->get('phone'), $otp);
            
            Mail::to($user->email)->send(new BountyUserOtp($user, $otp));

            $bodysms = 'Welcome to Defcomm!, Your OTP is ' . $otp . ' or use https://cloud.defcomm.ng/onboarding to join.';

            // $this->TermiiSms($request->phone, $bodysms);
            return response()->json(['status' => 200, 'message' => 'OTP has been sent', 'otp' => $otp], 200);
        } else {
            return response()->json(['status' => 400, 'error' => "This user does not exist."], 400);
        }
    }

    // Login user
    public function login(Request $request)
    {
        if($request->userlogin && $request->password){
            $user = BountyUser::where('email', $request->userlogin)->orWhere('username', $request->userlogin)->first();

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => '400',
                    'message' => 'Invalid credentials.',
                    'data' => null, // full list of field errors
                ], 401);
            }

            if ($user->status == "pending") {
                return response()->json([
                    'status' => '400',
                    'message' => 'Your account is not verify yet.',
                    'data' => null, // full list of field errors
                ], 401);
            }

            if ($user->status == "block") {
                return response()->json([
                    'status' => '400',
                    'message' => 'Your account is block.',
                    'data' => null, // full list of field errors
                ], 401);
            }

            // $token = $user->createToken('bounty_token')->plainTextToken;
            $otp = rand(1000, 9999);
            $user->update(['otp' => $otp]);
            Mail::to($user->email)->send(new BountyUserOtp($user, $otp));
            return response()->json([
                'status' => '200',
                'message' => 'Waiting for OTP verification',
                // 'token' => $token,
                // 'user' => $user,
            ], 201);
        }else{
            return response()->json([
                'status' => '400',
                'message' => 'Please enter your credentials.',
                'data' => null, // full list of field errors
            ], 401);
        }
    }

    // Login user
    public function loginVerify(Request $request)
    {
        if($request->userlogin){
            $user = BountyUser::where('email', $request->userlogin)->orWhere('username', $request->userlogin)->first();

            if ($user->otp != $request->otp) {
                return response()->json([
                    'status' => '400',
                    'message' => 'Wrong OTP. Try again!',
                    'data' => null, // full list of field errors
                ], 401);
            }

            if (now()->diffInSeconds($user->updated_at) > 60) {
                return response()->json([
                    'status' => '400',
                    'message' => 'Token expired. Please login again',
                    'data' => null, // full list of field errors
                ], 401);
            }

            $token = $user->createToken('bounty_token')->plainTextToken;

            return response()->json([
                'status' => '200',
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user,
            ], 201);
        }else{
            return response()->json([
                'status' => '400',
                'message' => 'Please enter your credentials.',
                'data' => null, // full list of field errors
            ], 401);
        }
    }


    public function forgotPassword(Request $request)
    {
        $user = BountyUser::where('email', $request->userlogin)->orWhere('username', $request->userlogin)->first();

        if ($user) {
            $otp = rand(1000, 9999);
            $user->update(['otp' => $otp]);
            Mail::to($user->email)->send(new BountyUserOtp($user, $otp));

            return response()->json([
                'status' => '200',
                'message' => 'Waiting for OTP verification',
                // 'token' => $token,
                // 'user' => $user,
            ], 201);
        }

        return response()->json([
            'status' => '400',
            'message' => 'Please enter your credentials.',
            'data' => null, // full list of field errors
        ], 401);
    }

    public function resetPassword(Request $request)
    {

        $user = BountyUser::where('email', $request->userlogin)->orWhere('username', $request->userlogin)->first();

        if (!$user) {
            return response()->json([
                'status' => '400',
                'message' => 'Wrong User Id. Try again!',
                'data' => null, // full list of field errors
            ], 401);
        }

        if ($user->otp != $request->otp) {
            return response()->json([
                'status' => '400',
                'message' => 'Wrong OTP. Try again!',
                'data' => null, // full list of field errors
            ], 401);
        }

        if ($request->password != $request->password_confirm) {
            return response()->json([
                'status' => '400',
                'message' => 'Password does not match!',
                'data' => null, // full list of field errors
            ], 401);
        }

        if (now()->diffInSeconds($user->updated_at) > 60) {
            return response()->json([
                'status' => '400',
                'message' => 'Token expired. Please login again',
                'data' => null, // full list of field errors
            ], 401);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => '200',
            'message' => 'Password has been successfully reset',
        ], 201);
    }

    // Logout user
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    // Protected route example
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
