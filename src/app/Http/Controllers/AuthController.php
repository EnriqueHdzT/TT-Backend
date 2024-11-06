<?php

namespace App\Http\Controllers;


use App\Mail\RecuperarContrasena;
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

            $verificationUrl = url('/api/verify-email/' .($newUser->id));


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

                if (Staff::where('id', $user->id)->exists()) {

                    return response()->json(['token' => $token->plainTextToken, 'userType' => Staff::find($user->id)->first()->staff_type], 200);
                }

                return response()->json(['token' => $token->plainTextToken, 'userType' => null], 200);
            }

            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hubo un error en el servidor', 'error' => $e->getMessage()], 500);
        }
    }
    //Prueba de EMAIL

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

    public function keepAlive(Request $request)
    {
        try {
            $user = Auth::user();

            $tokenValue = $request->bearerToken();
            $tokenParts = explode('|', $tokenValue, 2);
            $token = $user->tokens()
                ->where('id', $tokenParts[0])
                ->where('token', hash('sha256', $tokenParts[1]))
                ->first();
            if (!$token || $token->name !== 'SessionToken' || $token->expires_at <= now()) {
                $token?->delete();
                return response()->json(['message' => 'Sesión caducada'], 401);
            }

            $token->expires_at = now()->addMinutes(15);
            $token->save();

            return response()->json(['message' => 'Sesión actualizada'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hubo un error en el servidor'], 500);
        }
    }

    public function recuperarPassword(Request $request){
        // Validar el correo electrónico
        $request->validate([
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@alumno\.ipn\.mx$/'
        ]);

        // Verificar si el usuario existe
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Correo no encontrado'], 404);
        }

        // URL de recuperación de contraseña usando el ID del usuario
        $resetUrl = url('http://localhost:5174/recuperar/'.$user->id);

        // Enviar correo electrónico
        Mail::to($user->email)->send(new RecuperarContrasena($user, $resetUrl));

        return response()->json(['message' => 'Enlace de recuperación enviado a tu correo.'], 200);
    }

    public function resetPassword(Request $request)
    {
        // Validar la nueva contraseña
        $validatedData = $request->validate([
            'id' => 'required|exists:users,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verificar si el usuario existe
        $user = User::find($validatedData['id']);
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Actualizar la contraseña
        $user->update([
            'password' => $validatedData['password'],
        ]);

        return response()->json(['message' => 'Contraseña actualizada exitosamente'], 200);
    }

}
