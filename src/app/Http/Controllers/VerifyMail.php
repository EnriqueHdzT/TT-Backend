<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    // Aquí la función VerifyMail está correctamente dentro de la clase
    public function VerifyMail($token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 400);
        }

        // Marcar el correo como verificado
        $user->email_verified_at = now();
        $user->verification_token = null; // Eliminar el token de verificación
        $user->save();

        return response()->json(['message' => 'Correo verificado exitosamente'], 200);
    }
}
