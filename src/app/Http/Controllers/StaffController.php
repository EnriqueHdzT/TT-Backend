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
            'staff_id' => 'required|string|unique:staff,staff_id',
            'precedence' => 'required|string',
            'academy' => 'required|string',
            'email' => 'required|string|unique:users,email',
        ]);

        // Crear el nuevo usuario
        $user = new User();
        $user->email = $request->email;
        $password = Str::random(12);
        $user->password = bcrypt($password);
        $user->save();

        // Crear el profesor asociado
        $staff = new Staff();
        // Asignar otros campos del profesor si es necesario
        $staff->user_id = $user->id;
        $staff->lastname = $request->first_lastName;
        $staff->second_lastname = $request->second_lastName;
        $staff->name = $request->name;
        $staff->staff_id = $request->staff_id;
        $staff->precedence = $request->precedence;
        $staff->academy = $request->academy;

        $staff->save();

        return response()->json(['message' => 'Profesor creado exitosamente'], 201);
    }

    public function readStaff($id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['message' => 'Profesor no encontrado'], 404);
        }

        $staffData = $staff->toArray();
        unset($staffData['user_id']);
        unset($staffData['updated_at']);
        unset($staffData['created_at']);

        return response()->json(['staff' => $staffData], 200);
    }

    public function readStaffs()
    {
        $staffs = Staff::all();
        $formattedStaffs = [];

        foreach ($staffs as $staff) {
            $staffData = $staff->toArray();
            unset($staffData['user_id']);
            unset($staffData['updated_at']);
            unset($staffData['created_at']);
            $formattedStaffs[] = $staffData;
        }

        return response()->json(['staffs' => $formattedStaffs], 200);
    }

    public function updateStaff(Request $request, $id)
    {
        $request->validate([
            'first_lastName' => 'required|string',
            'second_lastName' => 'required|string',
            'name' => 'required|string',
            'staff_id' => 'required|string|unique:staff,staff_id,' . $id,
            'precedence' => 'required|string',
            'academy' => 'required|string',
        ]);

        $staff = Staff::find($id);

        if (!$staff) {
            return response()->json(['message' => 'profesor no encontrado'], 404);
        }

        $staff->lastname = $request->first_lastName;
        $staff->second_lastname = $request->second_lastName;
        $staff->name = $request->name;
        $staff->staff_id = $request->staff_id;
        $staff->precedence = $request->precedence;
        $staff->academy = $request->academy;
        $staff->save();

        return response()->json(['message' => 'Datos del profesor actualizados exitosamente'], 200);
    }

    public function deleteStaff($id) {
        $staff = Staff::find($id);

        if (!$staff) {    
            return response()->json(['message' => 'Profesor no encontrado'], 404);
        }

        $user = User::find($staff->user_id);
        $user->delete();
        $staff->delete();
        return response()->json(['message' => 'Profesor eliminado exitosamente'], 200);
    }
}
