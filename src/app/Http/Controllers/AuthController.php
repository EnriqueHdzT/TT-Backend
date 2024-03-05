<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $token = Auth::user()->createToken('AuthToken')->plainTextToken;
            return response()->json(['token' => $token], 200);
        }
    
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request) {
        auth()->user()->tokens()->delete();
        return [
            'message' => 'Logged Out'
        ];
    }
}
