<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\NotifyUserAccountMail;
use Illuminate\Support\Facades\Mail;

class AccountController extends Controller
{
    /**
     * Admin creates a new user.
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'role' => 'required',
            // 'image' => 'nullable|image|max:2048' // Allow image uploads
        ]);

        // // Handle image upload
        // // $imagePath = $request->file('image') ? $request->file('image')->store('profile', 'public') : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make('#Password123')
            // 'image' => $imagePath,
            // 'created_by' => auth()->id(),
        ]);


        $loginLink = config('custom.login_link');

        // send email notif
        Mail::to($request->email)->send(new NotifyUserAccountMail($user,'#Password123', $loginLink));

        return response()->json([
            'message' => 'User created successfully!',
            'user' => $user
        ], 201);

        // return response()->json([
        //     'message' =>"test response"
        // ],201);
    }

}
