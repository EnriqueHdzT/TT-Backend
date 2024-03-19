<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Staff;

class UsersController extends Controller
{
    public function searchUsers(Request $request) {
        $request->validate([
            'field' => 'required|string',
            'page' => 'required|int'
        ]);
    }

    public function getUsers(Request $request) {
        $request->validate([
            'filters' => 'required|array',
            'filters.usrType' => 'required|in:Student,Staff,All',
            'filters.precedence' => 'in:Internal,External',
            'filters.academy' => 'string',
            'filters.career' => 'in:ISW,IIA,LCD',
            'filters.curriculum' => 'date_format:Y|in:1999,2009,2020',
            'page' => 'required|int|min:1'
        ]);

        $filters = $request->filters;
        $page = $request->page;

        $totalPages = 0;
        $wantedStudents = 9;
        $usersResponse = [];

        // Get users based on filters
        if($filters['usrType'] === 'All') {
            $usersResponse = User::orderBy('created_at', 'desc')
            ->latest()
            ->skip(($page-1)*$wantedStudents)
            ->take($page*$wantedStudents)
            ->with('student')
            ->with('staff')
            ->get();
            $totalPages = ceil(User::count()/9);
        } elseif($filters['usrType'] === "Staff") {
            if(!array_key_exists("precedence", $filters)){
                $usersResponse = Staff::orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$wantedStudents)
                ->take($page*$wantedStudents)
                ->get();
                $totalPages = ceil(Staff::count()/9);
            } elseif($filters['precedence'] === "External"){
                $usersResponse = Staff::where('precedence', '!=', 'ESCOM')
                ->orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$wantedStudents)
                ->take($page*$wantedStudents)
                ->get();
                $totalPages = ceil(Staff::where('precedence', '!=', 'ESCOM')->count()/9);
            } elseif($filters['precedence'] === "Internal" && array_key_exists("academy", $filters)){
                $usersResponse = Staff::where('precedence', 'ESCOM')
                ->where('academy', $filters['academy'])
                ->orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$wantedStudents)
                ->take($page*$wantedStudents)
                ->get();
                $totalPages = ceil(Staff::where('precedence', '!=', 'ESCOM')->where('academy', $filters['academy'])->count()/9);
            }
            
        } elseif($filters['usrType'] === "Student") {
            if(!array_key_exists("career", $filters) && !array_key_exists("curriculum", $filters)){
                $usersResponse = Student::orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$wantedStudents)
                ->take($page*$wantedStudents)
                ->get();
                $totalPages = ceil(Student::count()/9);
            } elseif(array_key_exists("career", $filters) && !array_key_exists("curriculum", $filters)){
                $usersResponse = Student::where('career', $filters['career'])
                ->orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$wantedStudents)
                ->take($page*$wantedStudents)
                ->get();
                $totalPages = ceil(Student::where('career', $filters['career'])->count()/9);
            } else if(array_key_exists("career", $filters) && array_key_exists("curriculum", $filters)){
                $usersResponse = Student::where('career', $filters['career'])
                ->where('curriculum', $filters['curriculum'])
                ->orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$wantedStudents)
                ->take($page*$wantedStudents)
                ->get();
                $totalPages = ceil(Student::where('career', $filters['career'])->where('curriculum', $filters['curriculum'])->count()/9);
            }
        }

        // Validate if users where found
        if(count($usersResponse) === 0){
            return response()->json(['message' => 'Usuarios no encontrados'], 404);    
        }

        foreach($usersResponse as $user){
            unset($user['name']);
            unset($user['email_verified_at']);
            unset($user['created_at']);
            unset($user['updated_at']);
            if(!$user['staff']){
                unset($user['staff']);
                unset($user['student']['id']);
                unset($user['student']['user_id']);
                unset($user['student']['profile_image']);
                unset($user['student']['altern_email']);
                unset($user['student']['phone_number']);
                unset($user['student']['created_at']);
                unset($user['student']['updated_at']);
            } else {
                unset($user['student']);
                unset($user['staff']['id']);
                unset($user['staff']['user_id']);
                unset($user['staff']['profile_image']);
                unset($user['staff']['altern_email']);
                unset($user['staff']['phone_number']);
                unset($user['staff']['created_at']);
                unset($user['staff']['updated_at']);
            }
        }
        $usersResponse['numPages'] = $totalPages;
        return $usersResponse;
    }
}