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
            'first_lastName' => 'required|string',
            'second_lastName' => 'required|string',
            'name' => 'required|string',
            'student_id' => 'required|string|unique:students,student_id',
            'career' => 'required|in:ISW,IIA,LCD',
            'curriculum' => 'required|date_format:Y',
            'email' => 'required|string|unique:users,email',
        ]);

        // Crear el nuevo usuario
        $user = new User();
        $user->email = $request->email;
        $user->password = bcrypt(Str::random(12));
        $user->save();

        // Crear el estudiante asociado
        $student = new Student();
        // Asignar otros campos del estudiante si es necesario
        $student->id = $user->id;
        $student->lastname = $request->first_lastName;
        $student->second_lastname = $request->second_lastName;
        $student->name = $request->name;
        $student->student_id = $request->student_id;
        $student->career = $request->career;
        $student->curriculum = $request->curriculum;

        $student->save();

        return response()->json(['message' => 'Estudiante creado exitosamente'], 201);
    }

    public function readStudent($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }

        $studentData = $student->toArray();
        unset($studentData['id']);
        unset($studentData['updated_at']);
        unset($studentData['created_at']);

        return response()->json(['student' => $studentData], 200);
    }

    public function readStudents()
    {
        $students = Student::all();
        $formattedStudents = [];

        foreach ($students as $student) {
            $studentData = $student->toArray();
            unset($studentData['id']);
            unset($studentData['updated_at']);
            unset($studentData['created_at']);
            $formattedStudents[] = $studentData;
        }

        return response()->json(['students' => $formattedStudents], 200);
    }

    public function updateStudent(Request $request, $id)
    {
        $request->validate([
            'first_lastName' => 'required|string',
            'second_lastName' => 'required|string',
            'name' => 'required|string',
            'student_id' => 'required|string|unique:students,student_id,' . $id,
            'career' => 'required|in:ISW,IIA,ICD',
            'curriculum' => 'required|date_format:Y',
        ]);

        $student = Student::find($id);

        if (!$student) {
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }

        $student->lastname = $request->first_lastName;
        $student->second_lastname = $request->second_lastName;
        $student->name = $request->name;
        $student->student_id = $request->student_id;
        $student->career = $request->career;
        $student->curriculum = $request->curriculum;
        $student->save();

        return response()->json(['message' => 'Datos del estudiante actualizados exitosamente'], 200);
    }

    public function deleteStudent($id) {
        $student = Student::find($id);

        if (!$student) {    
            return response()->json(['message' => 'Estudiante no encontrado'], 404);
        }

        $user = User::find($student->id);
        $user->delete();
        $student->delete();
        return response()->json(['message' => 'Estudiante eliminado exitosamente'], 200);
    }
}
