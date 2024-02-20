<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Staff;

class StaffController extends Controller
{
    public function createStaff(Request $request){
        $request->validate([
            'first_lastName' => 'required|string',
            'second_lastName' => 'required|string',
            'name' => 'required|string',
            'staff_ID' => 'required|string|unique:students,student_ID',
            'school' => 'required|string',
            'academy' => 'required|string',
            'email' => 'required|string|unique:users,email',
        ]);

        // Crear el nuevo usuario
        $user = new User();
        $user->email = $request->email;
        $password = Str::random(12);
        $user->password = bcrypt($password);
        $user->save();

        // Crear el estudiante asociado
        $staff = new Staff();
        // Asignar otros campos del estudiante si es necesario
        $staff->user_id = $user->id;
        $staff->save();

        return response()->json(['message' => 'Profesor creado exitosamente'], 201);
    }

    public function deleteStaff(Request $request) {
        $request->validate([
            'id' => 'required|Integer',
        ]);
        $staff = Staff::find($request->id);

        if (!$staff) {    
            return response()->json(['message' => 'Profesor no encontrado'], 404);
        }

        $staff->delete();
        return response()->json(['message' => 'Profesor eliminado exitosamente'], 200);
    }
}
