<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request) {
        $fields = $request->validate([
            'first_lastName' => 'required|string',
            'second_lastName' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'usr_ID' => 'required|string',
            'password' => 'required|string|confirmed',
        ]);

        $user = User::create([
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
        ]);

        $token = $user->createToken('some')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    public function logout(Request $request) {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'Logged Out'
        ];
    }
}
