<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            'students' => 'required|array|max:4',
            'students.*.email' => 'required|string',
            'students.*.student_ID' => 'required|string',
            'staffs' => 'required|array|max:2',
            'staffs.*.email' => 'required|string',
            'staffs.*.name' => 'string',
            'staffs.*.first_lastName' => 'string',
            'staffs.*.second_lastName' => 'string',
            'staffs.*.staff_ID' => 'string',
            'staffs.*.precedence' => 'string',
            'staffs.*.academy' => 'string',
            'staffs.*.pdf' => 'file|mimes:pdf',
            'keywords' => 'required|array|max:4',
            'keywords.*.keyword' => 'required|string',
            'keywords.*.subject' => 'string',
            'keywords.*.department' => 'string',
            'keywords.*.academy' => 'string',
            'pdf' => 'file|mimes:pdf',
        ]);

        // Validate and process students
        $studentIDs = [];
        $studentEmails = [];
        foreach ($request->students as $student) {
            $existingStudent = User::where('email', $student['email'])->first();

            if ($existingStudent) {
                $student['name'] = $existingStudent->student['name'];
                $student['first_lastName'] = $existingStudent->student['first_lastName'];
                $student['second_lastName'] = $existingStudent->student['second_lastName'];
                $student['student_ID'] = $existingStudent->student['student_ID'];
                $student['career'] = $existingStudent->student['career'];
                $student['curriculum'] = $existingStudent->student['curriculum'];

            } else {
                $studentsValidator = Validator::make($student, [
                    'email' => 'required|string',
                    'name' => 'required|string',
                    'first_lastName' => 'required|string',
                    'second_lastName' => 'required|string',
                    'student_ID' => 'required|string',
                    'career' => 'required|in:ISW,IIA,ICD',
                    'curriculum' => 'required|date_format:Y',
                ]);
                
                if($studentsValidator->fails()){
                    $errors = $studentsValidator->errors()->toArray();
                    return response()->json([$errors], 400);
                }
                $newUser = new User();
                $newUser->email = $student['email'];
                // TODO : Manage what to do with passwords
                $newUser->password = "ghola";
                $newUser->save();
                
                $newStudent = new Student();
                $newStudent->user_id = $newUser->id;
                $newStudent->name = $student['first_lastName'];
                $newStudent->lastname = $student['second_lastName'];
                $newStudent->second_lastname = $student['name'];
                $newStudent->student_ID = $student['student_ID'];
                $newStudent->career = $student['career'];
                $newStudent->curriculum = $student['curriculum'];
                $newStudent->save();
            }
            $studentIDs[] = $student['student_ID'];
            $studentEmail = $student['email'];
            $studentEmails[] = $student['email'];
        }
        
        // Validate students duplicity
        $uniqueStudentIDs = array_unique($studentIDs);
        $uniqueStudentEmails = array_unique($studentEmails);
        if (count($studentIDs) !== count($uniqueStudentIDs) || count($studentEmails) !== count($uniqueStudentEmails)) {
            return response()->json(['error' => 'Duplicate student IDs or emails'], 400);
        }

        $staffs = [];
        $staffIDs = [];
        $staffEmails = [];
        $hasESCOM = false;
        foreach ($request->staffs as $staff) {
            $staffEmail = $staff['email'];
            $staffIDs[] = $staff['staff_ID'];
            $staffEmails[] = $staff['email'];
            if ($staff['precedence'] === 'ESCOM') {
                $hasESCOM = true;
            }
            $existingStaff = User::where('email', $staffEmail)->first();

            if ($existingStaff) {
                $staffs[] = $existingStaff->toArray();
            } else {
                $staffs[] = $staff;
            }
        }

        // Validate staffs duplicity
        $uniqueStaffIDs = array_unique($staffIDs);
        $uniqueStaffEmails = array_unique($staffEmails);
        if (count($staffIDs) !== count($uniqueStaffIDs) || count($staffEmails) !== count($uniqueStaffEmails)) {
            return response()->json(['error' => 'Duplicate staff IDs or emails'], 400);
        }
        if (!$hasESCOM) {
            return response()->json(['error' => 'At least one staff member must have precedence ESCOM'], 400);
        }
        return response()->json($request->students, 201);
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
            'student_ID' => 'required|string|unique:students,student_ID',
            'staff_ID' => 'required|string|unique:staff,staff_ID',
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