<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Student;

class StudentController extends Controller
{
    public function createStudent(Request $request)
    {
        $request->validate([
            'first_lastName' => 'string',
            'second_lastName' => 'string',
            'name' => 'required|string',
            'student_ID' => 'required|string|unique:students,student_ID',
            'career' => 'required|in:ISW,IIA,ICD',
            'curriculum' => 'required|date_format:Y',
            'email' => 'required|string|unique:users,email',
        ]);

        // Crear el nuevo usuario
        $user = new User();
        $user->email = $request->email;
        $password = Str::random(12);
        $user->password = bcrypt($password);
        $user->save();

        // Crear el estudiante asociado
        $student = new Student();
        // Asignar otros campos del estudiante si es necesario
        $student->user_id = $user->id;
        $student->save();

        return response()->json(['message' => 'Estudiante creado exitosamente'], 201);
    }

    public function deleteStudent(Request $request) {
        $request->validate([
            'id' => 'required|Integer',
        ]);
        $student = Student::find($request->id);

        if (!$student) {    
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }

        $student->delete();
        return response()->json(['message' => 'Estudiante eliminado exitosamente'], 200);
    }
}
