<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Student;
use App\Models\RegisterToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        // Valores y sus caracteristicas a cumplir en el request
        $rules = [
            'first_lastName' => 'required|string',
            'second_lastName' => 'string',
            'name' => 'required|string',
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@alumno\.ipn\.mx$/|confirmed',
            'usr_id' => 'required|string',
            'career' => 'required|in:ISW,IIA,LCD',
            'curriculum' => 'required|in:2009,2020|date_format:Y',
            'password' => 'required|string|size:64',
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return response()->json(['message' => 'Los datos no cumplen con la estructura no esperada'], 422);
        }

        // En caso de que el correo ya exista se envia un correo mencionado esto al usuario
        if(User::where('email', $request->email)->first()){
            // TODO - @EMAIL Agregar funcion de envio de correo
            return response([], 200);
        }

        $newUser = User::create([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $newUser->save();

        $newStudent = new Student;
        $newStudent->user_id = $newUser->id;
        $newStudent->name = $request->name;
        $newStudent->lastname = $request->first_lastName;
        $newStudent->second_lastname = $request->second_lastName ? $request->second_lastName : null ;
        $newStudent->student_id = $request->usr_id;
        $newStudent->career = $request->career;
        $newStudent->curriculum = $request->curriculum;
        $newStudent->save();

        // Se genera token y parametro del URL que se enviara al usuario para validar su correo
        $token = Str::random(60);
        $newRegisterToken = new RegisterToken;
        $newRegisterToken->email = $request->email;
        $newRegisterToken->token = $token;
        $newRegisterToken->save();

        $userToken = Crypt::encryptString($request->email + $token);

        // TODO - @EMAIL Agregar funcion que envie el token

        return response([], 200);
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