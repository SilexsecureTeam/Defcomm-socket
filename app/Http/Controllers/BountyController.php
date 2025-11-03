<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\BountyUser;
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
