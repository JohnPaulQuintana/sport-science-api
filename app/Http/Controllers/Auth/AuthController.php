<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // test the api
    public function api_test()
    {
        return response()->json(['status'=>200, 'message'=>"api is now available.."]);
    }
    // Login API
    public function login(Request $request)
    {
        // Validate request
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Check if user exists
        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email not found.'
            ], 404);
        }

        // Attempt authentication
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect password.'
            ], 401);
        }

        // Generate token
        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        // Validate Email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'reset_link' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email address.'
            ], 400);
        }

        // Generate Reset Token
        $token = Password::createToken(User::where('email', $request->email)->first());

        // Send Reset Email
        Mail::to($request->email)->send(new ResetPasswordMail($token,$request->reset_link));

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset link sent to your email.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        // Validate request
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        // Get all password reset entries and find the correct one using Hash::check
        $resetEntries = \DB::table('password_reset_tokens')->get();

        $resetData = $resetEntries->first(function ($entry) use ($request) {
            return Hash::check($request->token, $entry->token);
        });

        if (!$resetData) {
            return response()->json(['message' => 'Invalid or expired token'], 400);
        }

        // Find the user by email
        $user = User::where('email', $resetData->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the used token
        \DB::table('password_reset_tokens')->where('email', $resetData->email)->delete();

        return response()->json(['message' => 'Password reset successful']);
    }


    public function logout(Request $request)
    {
        // Get authenticated user
        $user = $request->user();

        // Revoke the user's current token
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

}
