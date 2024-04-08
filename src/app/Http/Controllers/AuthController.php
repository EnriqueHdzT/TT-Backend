<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        $rules = [
            'first_lastName' => 'required|string',
            'second_lastName' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|string|confirmed',
            'usr_id' => 'required|string',
            'career' => 'required|in:ISW,IIA,LCD',
            'curriculum' => 'required|in:1999,2009,2020|date_format:Y',
            'password' => 'required|string|min:8|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            return response()->json(['message' => 'Estructura no esperados'], 422);
        }

        if(User::where('email', $request->email)->first()){
            return response()->json(['message' => 'Correo ya registrado en el sistema'], 409);
        }
        if(Student::where('student_id', $request->usr_id)->first()){
            return response()->json(['message' => 'Boleta ya registrada en el sistema'], 409);
        }

        $newUser = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $newUser->is_register = true;
        $newUser->save();

        $newStudent = new Student;
        $newStudent->user_id = $newUser->id;
        $newStudent->name = $request->name;
        $newStudent->lastname = $request->first_lastName;
        $newStudent->second_lastname = $request->second_lastName;
        $newStudent->student_id = $request->usr_id;
        $newStudent->career = $request->career;
        $newStudent->curriculum = $request->curriculum;
        $newStudent->save();

        $token = $newUser->createToken('some')->plainTextToken;

        $response = [
            'user' => $newUser,
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
        $user = Auth::user();
        $tokenValue = $request->bearerToken();
        if ($user && $tokenValue) {
            $tokenParts = explode('|', $tokenValue, 2);
            $deleted = $user->tokens()
                ->where('id', $tokenParts[0])
                ->where('token', hash('sha256', $tokenParts[1]))
                ->delete();
            if ($deleted) {
                return response()->json([], 204);
            }
        }

        return response()->json([], 404);
    }
}