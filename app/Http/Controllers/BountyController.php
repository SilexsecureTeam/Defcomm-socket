<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\BountyUser;
use App\Mail\BountyUserOtp;
use Illuminate\Http\Request;
use App\Mail\BountyUserVerify;
use App\Models\BountyCategory;
use App\Models\BountyUserReport;
use App\Models\BountyUserProgram;
use Illuminate\Support\Facades\DB;
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
        if ($request->userlogin && $request->password) {
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
        } else {
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
        if ($request->userlogin) {
            $user = BountyUser::where('email', $request->userlogin)->orWhere('username', $request->userlogin)->first();

            if ($user->otp != $request->otp) {
                return response()->json([
                    'status' => '400',
                    'message' => 'Wrong OTP. Try again!',
                    'data' => null, // full list of field errors
                ], 401);
            }

            if (now()->diffInSeconds($user->updated_at) > 120) {
                return response()->json([
                    'status' => '400',
                    'message' => 'Token expired. Please login again',
                    'data' => null, // full list of field errors
                ], 401);
            }

            if ($user->emailVerify == "false") {
                $user->update(["emailVerify" => "true"]);
            }

            $token = $user->createToken('bounty_token')->plainTextToken;

            return response()->json([
                'status' => '200',
                'message' => 'Login successful',
                'token' => $token,
                'user' => $user,
            ], 201);
        } else {
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
        $user = BountyUser::find(auth()->user()->id);
        $data = [
            'id'          => encryptHelper($user->id),
            'firstName'   => $user->firstName,
            'lastName'    => $user->lastName,
            'username'    => $user->username,
            'email'       => $user->email,
            'country'     => $user->country,
            'phone'       => $user->phone,
            'zipcode'     => $user->zipcode,
            'timezone'    => $user->timezone,
            'photo'       => $user->photo,
            'bio'         => $user->bio,
            'point'     => $user->report->sum('point'),
            'balance'     => $user->report->sum('amount'),
            'status'      => $user->status,
            'created_at'  => $user->created_at,
            'updated_at'  => $user->updated_at,
            'emailVerify' => $user->emailVerify,
        ];

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function program()
    {
        $datas = BountyUserProgram::where('status', 'active')->get();
        $data = [];
        foreach ($datas as $dt) {
            $data[] = [
                'id' => encrypt($dt->id),
                'title' => $dt->title,
                'detail' => $dt->detail
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function category()
    {
        $datas = BountyCategory::where('status', 'active')->get();
        $data = [];
        foreach ($datas as $dt) {
            $data[] = [
                'id' => $dt->id,
                'label' => $dt->label,
                'description' => $dt->description,
                'sub' => $dt->sub
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function report(Request $request)
    {
        $paths = [];

        // Check if multiple files were uploaded
        if ($request->hasFile('attachment')) {

            foreach ($request->file('attachment') as $file) {
                if ($file->isValid()) {

                    // Generate unique filename
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    // Move file to public/bounty
                    $file->move(public_path('bounty'), $filename);

                    // Save path
                    $paths[] = 'bounty/' . $filename;
                }
            }
        }

        // Convert paths to JSON string for storage
        $pathsJson = json_encode($paths);

        $data = BountyUserReport::create([
            'ref'          => 'RBT' . strtoupper(uniqid()),
            'user_id'      => auth()->user()->id,
            'program_id'   => $request->program_id ? decrypt($request->program_id) : 1,
            'title'        => $request->title,
            'detail'       => $request->detail,
            'attachment'   => $pathsJson,
            'category'     => $request->category,
            'category_sub' => $request->category_sub,
            'severity'     => $request->severity,
            'status'       => 'new',
        ]);

        return response()->json([
            'status'  => '200',
            'message' => 'Record created',
            'data'    => [
                'id'  => encrypt($data->id),
                'ref' => $data->ref,
            ]
        ], 201);
    }

    public function reportUpdate(Request $request)
    {
        $report = BountyUserReport::findOrFail(decrypt($request->id));

        // Allowed fields
        $fillable = [
            'title',
            'detail',
            'category',
            'category_sub',
            'severity',
            'status',
            'program_id',
        ];

        // Prepare update data
        $updateData = [];

        foreach ($fillable as $field) {
            if ($request->has($field) && $request->$field !== null) {

                // decrypt program_id if sent
                if ($field === 'program_id') {
                    $updateData[$field] = decrypt($request->$field);
                } else {
                    $updateData[$field] = $request->$field;
                }
            }
        }

        // ----------------------------
        // 📌 ATTACHMENT UPDATE HANDLING
        // ----------------------------

        if ($request->hasFile('attachment')) {

            // 1️⃣ DELETE OLD FILES
            $oldAttachments = json_decode($report->attachment, true) ?? [];

            foreach ($oldAttachments as $oldFile) {
                $filePath = public_path($oldFile);

                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            // 2️⃣ UPLOAD NEW FILES
            $newPaths = [];
            foreach ($request->file('attachment') as $file) {
                if ($file->isValid()) {

                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                    $file->move(public_path('bounty'), $filename);

                    $newPaths[] = 'bounty/' . $filename;
                }
            }

            // Store new attachments as JSON
            $updateData['attachment'] = json_encode($newPaths);
        }

        // ----------------------------
        // 📌 UPDATE RECORD
        // ----------------------------
        if (!empty($updateData)) {
            $report->update($updateData);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Report updated successfully',
            'data'    => $report,
        ]);
    }


    public function reportLog()
    {
        $datas = BountyUserReport::where('user_id', auth()->user()->id)->orderBy('created_at', "DESC")->get();
        $data = [];

        foreach ($datas as $dt) {
            $attachments = collect(json_decode($dt->attachment))
                ->map(function ($file) {
                    return url($file); // or asset('storage/...'), depending on your path
                })
                ->toArray();

            $data[] = [
                'id' => encrypt($dt->id),
                'ref' => $dt->ref,
                'program' => $dt->program->title,
                'title' => $dt->title,
                'detail' => $dt->detail,
                'attachment' => $attachments,
                'category' => $dt->categori->label,
                'category_sub' => $dt->categorySub->label,
                'severity' => $dt->severity,
                'point' => $dt->point,
                'amount' => $dt->amount,
                'status' => $dt->status,
                'created_at' => $dt->created_at,
                'updated_at' => $dt->updated_at,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function reportInfo()
    {
        $datas = BountyUserReport::where('user_id', auth()->user()->id)->orderBy('created_at', "DESC");
        $data = [
            "report" => $datas->count(),
            "reportnew" => $datas->where('status', 'new')->count(),
            "reportreview" => $datas->where('status', 'review')->count(),
            "reportaccept" => $datas->where('status', 'accept')->count(),
            "reportreject" => $datas->where('status', 'reject')->count(),
            "reportfix" => $datas->where('status', 'fix')->count(),
            "reportclose" => $datas->where('status', 'close')->count(),
            "reportamount" => $datas->sum('amount'),
            "reportpoint" => $datas->sum('point'),
        ];

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }

    public function leaderboard()
    {
        $datas = BountyUserReport::select(
            'user_id',
            DB::raw('SUM(point) as total_points'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('COUNT(*) as total_reports')
        )
            ->groupBy('user_id')
            ->orderByDesc('total_points')
            ->get();

        $data = [];

        foreach ($datas as $ky => $dt) {
            $data[] = [
                'no' => $ky + 1,
                'firstName' => $dt->user->firstName,
                'lastName' => $dt->user->lastName,
                'username' => $dt->user->username,
                'photo' => $dt->user->photo,
                'total_points' => $dt->total_points,
                'total_amount' => $dt->total_amount,
                'total_reports' => $dt->total_reports,
            ];
        }

        return response()->json(
            [
                'status' => '200',
                'message' => 'Record listed',
                'data' => $data
            ],
            201
        );
    }
}
