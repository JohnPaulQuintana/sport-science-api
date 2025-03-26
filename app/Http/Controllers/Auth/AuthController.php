<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // test the api
    public function api_test()
    {
        return response()->json(['status' => 200, 'message' => "api is now available.."]);
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
        Mail::to($request->email)->send(new ResetPasswordMail($token, $request->reset_link));

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

    public function update(Request $request)
    {
        // Remove 'password' and 'image' from validation if they are missing from request
        $rules = [
            'id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ];

        if ($request->has('password') && !empty($request->password)) {
            $rules['password'] = 'string|min:6';
        }

        if ($request->hasFile('image')) {
            $rules['image'] = 'image|max:2048';
        }

        $validatedData = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Find the user
            $user = User::findOrFail($validatedData['id']);

            // Track if something changed
            $hasChanges = false;

            // Update name and email if changed
            if ($user->name !== $validatedData['name']) {
                $user->name = $validatedData['name'];
                $hasChanges = true;
            }

            if ($user->email !== $validatedData['email']) {
                $user->email = $validatedData['email'];
                $hasChanges = true;
            }

            // Handle image upload only if provided
            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if (!empty($user->profile)) {
                    Storage::disk('public')->delete($user->profile);
                }

                // Store new image
                $imagePath = $request->file('image')->store('profile', 'public');
                $user->profile = $imagePath;
                $hasChanges = true;
            }

            // Update password only if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $hasChanges = true;
            }

            // If nothing was updated, return a separate response
            if (!$hasChanges) {
                DB::rollBack();
                return response()->json([
                    'message' => 'No changes made.',
                    'user' => $user
                ], 200);
            }

            $user->save();
            DB::commit();

            return response()->json([
                'message' => 'User updated successfully!',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong! ' . $e->getMessage()], 500);
        }
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
