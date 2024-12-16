<?php

namespace App\Http\Controllers;


use App\Mail\MensajeDeContacto;
use App\Mail\RecuperarContrasena;
use App\Models\ContenidoPrincipal;
use App\Models\User;
use App\Models\Student;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\BienvenidoVerifMail;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Valores y sus caracteristicas a cumplir en el request
            $rules = [
                'first_lastName' => 'required|string',
                'second_lastName' => 'string',
                'name' => 'required|string',
                'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@alumno\.ipn\.mx$/|confirmed',
                'usr_id' => 'required|string|size:10',
                'career' => 'required|in:ISW,IIA,LCD',
                'curriculum' => 'required|in:2009,2020|date_format:Y',
                'password' => 'required|string|size:64|confirmed',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->messages()], 422);
            }

            if (User::where('email', $request->email)->first()) {
                return response(['message' => 'Correo ya registrado'], 409);
            }

            $newUser = User::create([
                'email' => $request->email,
                'password' => $request->password,
                'email_is_verified' => false,
            ]);

            $token = $newUser->createToken('RegisterToken', []);
            $token->accessToken->expires_at = now()->addDay();
            $token->accessToken->save();

            $newUser->save();

            $newStudent = new Student;
            $newStudent->id = $newUser->id;
            $newStudent->name = $request->name;
            $newStudent->lastname = $request->first_lastName;
            $newStudent->second_lastname = $request->second_lastName ? $request->second_lastName : null;
            $newStudent->student_id = $request->usr_id;
            $newStudent->career = $request->career;
            $newStudent->curriculum = $request->curriculum;
            $newStudent->save();

            $verificationUrl = url('/api/verify-email/' . ($newUser->id));


            Mail::to($newUser->email)->send(new BienvenidoVerifMail($newUser, $verificationUrl));


            // TODO - @EMAIL Agregar funcion que envie email para verificar correo

            return response([], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $rules = [
                'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@(alumno\.)?ipn\.mx$/',
                'password' => 'required|string|size:64',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['message' => 'Los datos no cumplen con la estructura esperada'], 422);
            }

            $credentials = [
                'email' => $request->email,
                'password' => $request->password,
            ];

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                // TODO - Terminar de implementar cuando funcionalidad de correo este lista
                if (!$user->email_is_verified) {
                    return response()->json(['message' => 'El correo no ha sido verificado. Por favor revise su correo.'], 401);
                }
                $token = $user->createToken('SessionToken', []);
                $accessToken = $token->accessToken;
                $accessToken->update([
                    'expires_at' => now()->addMinutes(15),
                ]);
                $staff = $user->staff;
                return response()->json(['token' => $token->plainTextToken, 'userType' => $staff->staff_type], 200);
            }

            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hubo un error en el servidor', 'error' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            $tokenValue = $request->bearerToken();
            if ($user && $tokenValue) {
                $tokenParts = explode('|', $tokenValue, 2);
                $deleted = $user->tokens()
                    ->where('id', $tokenParts[0])
                    ->where('token', hash('sha256', $tokenParts[1]))
                    ->delete();
                if ($deleted) {
                    return response()->json(['message' => 'Cierre de sesión exitoso'], 204);
                }
            }

            return response()->json(['message' => 'Cierre de sesión fallido'], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hubo un error en el servidor'], 500);
        }
    }

    public function recuperarPassword(Request $request)
    {
        // Validar el correo electrónico
        $request->validate([
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@alumno\.ipn\.mx$/'
        ]);

        // Verificar si el usuario existe
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Correo no encontrado'], 404);
        }

        $user->remember_token = Str::random(60);
        $user->save();

        // URL de recuperación de contraseña usando el ID del usuario
        $resetUrl = url('http://localhost:5174/recuperar/' . $user->remember_token);


        // Enviar correo electrónico
        Mail::to($user->email)->send(new RecuperarContrasena($user, $resetUrl));

        return response()->json(['message' => 'Enlace de recuperación enviado a tu correo.'], 200);
    }

    public function resetPassword(Request $request)
    {
        // Validar la nueva contraseña
        $validatedData = $request->validate([
            'token' => 'required|string|exists:users,remember_token',
            'password' => 'required|string|size:64|confirmed',
        ]);

        // Verificar si el usuario existe
        $user = User::where('remember_token', $validatedData['token'])->first();
        if (!$user) {
            return response()->json(['message' => 'Token invalido o usuario no encontrado'], 404);
        }

        // Hashear la nueva contraseña usando bcrypt
        $hashedPassword = Hash::make($validatedData['password']);
        $user->password = $hashedPassword;
        $user->remember_token = null;
        $user->save();


        return response()->json(['message' => 'Contraseña actualizada exitosamente'], 200);
    }

    public function resetPasswordID(Request $request, $id)
    {
        // Validar la nueva contraseña
        $validatedData = $request->validate([
            'password' => 'required|string|size:64|confirmed',
        ]);

        // Verificar si el usuario existe
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        $hashedPassword = Hash::make($validatedData['password']);
        $user->password = $hashedPassword;
        $user->remember_token = null;
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada exitosamente'], 200);
    }

    public function recibiremail(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'message' => 'required|string'
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');

        try {
            Mail::to('franjav.cast@gmail.com')->send(new MensajeDeContacto($name, $email, $message));

            return response()->json(['message' => 'Correo enviado exitosamente'], 200);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                return response()->json(['message' => 'Error enviando el correo.', 'error' => $e->getMessage()], 500);
            } else {
                return response()->json(['message' => 'Error enviando el correo, por favor intente más tarde.'], 500);
            }
        }
    }
}
