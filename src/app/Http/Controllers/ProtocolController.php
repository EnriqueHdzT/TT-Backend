<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


use App\Models\User;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Protocol;


class ProtocolController extends Controller
{
    public function createProtocol(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'students' => 'required|array|min:1|max:4',
            'students.*.email' => 'required|string',
            'directors' => 'required|array|min:1|max:2',
            'directors.*.email' => 'required|string',
            'keywords' => 'required|array|min:1|max:4',
            'keywords.*.keyword' => 'required|string',
            'pdf' => 'file|mimes:pdf',
        ]);        

        // Validate and process students
        $studentIDs = [];
        $studentTableIDs = [];
        $studentEmails = [];
        foreach ($request->students as $student) {
            $existingStudent = User::where('email', $student['email'])->first();

            if ($existingStudent) {
                $student['name'] = $existingStudent->student['name'];
                $student['lastname'] = $existingStudent->student['lastname'];
                $student['second_lastname'] = $existingStudent->student['second_lastname'];
                $student['student_id'] = $existingStudent->student['student_id'];
                $student['career'] = $existingStudent->student['career'];
                $student['curriculum'] = $existingStudent->student['curriculum'];

                $studentTableIDs[] = $existingStudent->student['id'];
            } else {
                $studentsValidator = Validator::make($student, [
                    'email' => 'required|string',
                    'name' => 'required|string',
                    'lastname' => 'required|string',
                    'second_lastname' => 'required|string',
                    'student_id' => 'required|string',
                    'career' => 'required|in:ISW,IIA,ICD',
                    'curriculum' => 'required|in:2009,2020|date_format:Y',
                ]);
                
                if($studentsValidator->fails()){
                    $errors = $studentsValidator->errors()->toArray();
                    return response()->json([$errors], 400);
                }
                $newUser = new User();
                $newUser->email = $student['email'];
                $newUser->password = bcrypt(Str::random(12));
                $newUser->save();
                
                $newStudent = new Student();
                $newStudent->id = $newUser->id;
                $newStudent->name = $student['name'];
                $newStudent->lastname = $student['lastname'];
                $newStudent->second_lastname = $student['second_lastname'];
                $newStudent->student_id = $student['student_id'];
                $newStudent->career = $student['career'];
                $newStudent->curriculum = $student['curriculum'];
                $newStudent->save();

                $studentTableIDs[] = $newStudent['id'];
            }
            $studentIDs[] = $student['student_id'];
            $studentEmails[] = $student['email'];
        }
        
        // Validate students duplicity
        $uniqueStudentIDs = array_unique($studentIDs);
        $uniqueStudentEmails = array_unique($studentEmails);
        if (count($studentIDs) !== count($uniqueStudentIDs) || count($studentEmails) !== count($uniqueStudentEmails)) {
            return response()->json(['error' => 'Duplicated students'], 400);
        }

        $directorsIDs = [];
        $directorsTableIDs = [];
        $directorsEmails = [];
        $hasESCOM = false;
        foreach ($request->directors as $director) {
            $existingdirector = User::where('email', $director['email'])->first();

            if ($existingdirector) {
                $director['name'] = $existingdirector->staff['name'];
                $director['lastname'] = $existingdirector->staff['lastname'];
                $director['second_lastname'] = $existingdirector->staff['second_lastname'];
                $director['student_id'] = $existingdirector->staff['staff_id'];
                $director['precedence'] = $existingdirector->staff['precedence'];
                $director['academy'] = $existingdirector->staff['academy'];

                $directorsTableIDs[] = $existingdirector->staff['id'];
            } else {
                $directorValidator = Validator::make($director, [
                    'email' => 'required|string',
                    'name' => 'required|string',
                    'lastname' => 'required|string',
                    'second_lastname' => 'required|string',
                    'staff_id' => 'required|string',
                    'precedence' => 'required|string',
                ]);
                
                if($directorValidator->fails()){
                    $errors = $directorValidator->errors()->toArray();
                    return response()->json([$errors], 400);
                }
                $newUser = new User();
                $newUser->email = $director['email'];
                $newUser->password = bcrypt(Str::random(12));
                $newUser->save();
                
                $newStaff = new Staff();
                $newStaff->id = $newUser->id;
                $newStaff->name = $director['name'];
                $newStaff->lastname = $director['lastname'];
                $newStaff->second_lastname = $director['second_lastname'];
                $newStaff->staff_id = $director['staff_id'];
                $newStaff->precedence = $director['precedence'];
                $newStaff->academy = $director['academy'];
                $newStaff->save();

                $directorsTableIDs[] = $newStaff['id'];
            }
            if($director['precedence'] === 'ESCOM'){
                $hasESCOM = true;
            }
            $directorsIDs[] = $director['staff_id'];
            $directorsEmails[] = $director['email'];
        }

        // Validate staffs duplicity
        $uniqueDirectorsIDs = array_unique($directorsIDs);
        $uniqueDirectorsEmails = array_unique($directorsEmails);
        if (count($directorsIDs) !== count($uniqueDirectorsIDs) || count($directorsEmails) !== count($uniqueDirectorsEmails)) {
            return response()->json(['error' => 'Duplicated staff'], 400);
        }
        if (!$hasESCOM) {
            return response()->json(['error' => 'At least one staff member must have precedence ESCOM'], 400);
        }

        $studentTableIDs = array_unique($studentTableIDs);
        $directorsTableIDs = array_unique($directorsTableIDs);

        $newProtocol = new Protocol;
        $newProtocol->title = $request->title;
        $newProtocol->keywords = json_encode($request->keywords);
        $newProtocol->save();
        $newProtocol->students()->attach($studentTableIDs);
        $newProtocol->directors()->attach($directorsTableIDs);

        return response()->json($request, 201);
    }

    public function readProtocol($id)
    {
        $protocol = Protocol::find($id);

        if (!$protocol) {
            return response()->json(['message' => 'Protocolo no encontrado'], 404);
        }

        return response()->json(['protocol' => $protocol], 200);
    }

    public function readProtocols()
    {
        $protocols = Protocol::all();
        $formattedProtocols = [];

        foreach ($protocols as $protocol) {
            $protocolData = $protocol->toArray();
            unset($protocolData['id']);
            unset($protocolData['keywords']);
            unset($protocolData['pdf']);
            unset($protocolData['updated_at']);
            unset($protocolData['created_at']);
            $formattedProtocols[] = $protocolData;
        }

        return response()->json(['protocols' => $formattedProtocols], 200);
    }

    public function updateProtocol(Request $request, $id)
    {
        $request->validate([
            'title_protocol' => 'required|string',
            'student_id' => 'required|string|unique:students,student_id',
            'staff_id' => 'required|string|unique:staff,staff_id',
            'keywords' => 'required|string',
            'protocol_doc' => 'required|binary',
        ]);

        $protocol = Protocol::find($id);

        if (!$protocol) {
            return response()->json(['message' => 'Protocolo no encontrado'], 404);
        }

        $protocol->title_protocol = $request->title_protocol;
        $protocol->student_ID = $request->student_ID;
        $protocol->staff_ID = $request->staff_ID;
        $protocol->keywords = $request->keywords;
        $protocol->protocol_doc = $request->protocol_doc;
        $protocol->save();

        return response()->json(['message' => 'Datos del protocolo cargados exitosamente'], 200);
    }

    public function deleteProtocol($id) {
        $protocol = Protocol::find($id);

        if (!$protocol) {    
            return response()->json(['message' => 'Protocolo no encontrado'], 404);
        }

        $protocol->delete();
        return response()->json(['message' => 'Protocolo eliminado exitosamente'], 200);
    }
}