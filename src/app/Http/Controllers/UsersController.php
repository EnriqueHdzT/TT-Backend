<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Student;
use App\Models\Staff;

class UsersController extends Controller
{
    protected $columnsToDoSearchOnNoSpace = [
        'students' => ['student_id', 'altern_email', 'phone_number', 'lastname', 'second_lastname', 'name'],
        'staff' => ['staff_id', 'altern_email', 'phone_number', 'lastname', 'second_lastname', 'name'],
        'users' => ['email']
    ];
    
    protected $columnsToDoSearchOnWithSpace = [
        'students' => ['lastname', 'second_lastname', 'name'],
        'staff' => ['lastname', 'second_lastname', 'name']
    ];
    
    protected $wantedUsers = 9;

    public function searchUsers(Request $request) {
        $request->validate([
            'field' => 'required|string',
            'page' => 'required|int'
        ]);

        $field = trim($request->field);
        $page = $request->page;

        $totalPages = 0;
        $usersResponse["staff"] = [];
        $usersResponse["students"] = [];
        $uniqueUserIds = [];

        // If not spaces are found
        if(strpos($field, ' ') === false){
            foreach($this->columnsToDoSearchOnNoSpace as $table => $columns){

                foreach($columns as $column) {

                    if ($table === 'users') {
                        // Perform the search in the users table
                        $usersFound = DB::table($table)
                            ->where(function($queryBuilder) use ($column, $field) {
                                $queryBuilder->where(DB::raw("LOWER(\"$column\")"), 'LIKE', strtolower($field) . '%');
                            })
                            ->get();

                        if (!empty($usersFound)) {
                            foreach ($usersFound as $user) {
                                if (!in_array($user->id, $uniqueUserIds)) {
                                    $uniqueUserIds[] = $user->id;
                                    $student = Student::where('user_id', $user->id)->first();
                                    $staff = Staff::where('user_id', $user->id)->first();
                                    $email = $user->email;

                                    if($staff){
                                        $user = $staff;
                                        $user->email = $email;
                                        unset($user->user_id);
                                        unset($user->birth_date);
                                        unset($user->altern_email);
                                        unset($user->phone_number);
                                        unset($user->created_at);
                                        unset($user->updated_at);
                                        $usersResponse["staff"][] = $user;
                                    } else {
                                        $user = $student;
                                        $user->email = $email;
                                        unset($user->user_id);
                                        unset($user->birth_date);
                                        unset($user->altern_email);
                                        unset($user->phone_number);
                                        unset($user->created_at);
                                        unset($user->updated_at);
                                        $usersResponse["students"][] = $user;
                                    }
                                }
                            }
                        }
                    } else {
                        $usersFound = DB::table($table)
                            ->where(function($queryBuilder) use ($column, $field) {
                                $queryBuilder->where(DB::raw("LOWER(\"$column\")"), 'LIKE', strtolower($field) . '%');
                            })
                            ->get();
                        
                        if (!empty($usersFound)) {
                            foreach ($usersFound as $userFound) {
                                $user = User::where('id', $userFound->id)->first();
                                if (!in_array($user->id, $uniqueUserIds)) {
                                    $uniqueUserIds[] = $user->id;
                                    $userFound->email = $user->email;
                                    unset($userFound->user_id);
                                    unset($userFound->birth_date);
                                    unset($userFound->altern_email);
                                    unset($userFound->phone_number);
                                    unset($userFound->created_at);
                                    unset($userFound->updated_at);
                                    $usersResponse[$table][] = $userFound;
                                }
                            }
                        }
                    }
                }

            }
        } 
        
        else {
            $searchTerms = explode(' ', $field);

            foreach ($this->columnsToDoSearchOnWithSpace as $table => $columns) {
                $usersResponse[$table] = [];

                // Generate permutations of the search terms
                $permutations = $this->generatePermutations($searchTerms);
                foreach ($permutations as $permutation) {
                    $usersFound = DB::table($table);
                
                    $usersFound->where(function ($query) use ($columns, $permutation) {
                        foreach ($permutation as $term) {
                            $query->where(function ($query) use ($columns, $term) {
                                foreach ($columns as $column) {
                                    $query->orWhere(DB::raw("LOWER(\"$column\")"), 'LIKE', strtolower($term) . '%');
                                }
                            });
                        }
                    });
                
            $usersFound = $usersFound->get()->toArray();
        
            // Check if any user was found
            if (!empty($usersFound)) {
                foreach ($usersFound as $user) {
                    if (!in_array($user->user_id, $uniqueUserIds)) {
                        $uniqueUserIds[] = $user->user_id;
                        unset($user->user_id);
                        unset($user->birth_date);
                        unset($user->altern_email);
                        unset($user->phone_number);
                        unset($user->created_at);
                        unset($user->updated_at);
                        $usersResponse[$table][] = $user;
                    }
                }
            }
        }
    }
        }
        return $usersResponse;
    }

    // Function to generate permutations of search terms
    private function generatePermutations($terms) {
        $result = [];
        $length = count($terms);
        $this->permute($terms, 0, $length - 1, $result);
        return $result;
    }

    private function permute(&$terms, $left, $right, &$result) {
        if ($left === $right) {
            $result[] = $terms;
        } else {
            for ($i = $left; $i <= $right; $i++) {
                $this->swap($terms[$left], $terms[$i]);
                $this->permute($terms, $left + 1, $right, $result);
                $this->swap($terms[$left], $terms[$i]);
            }
        }
    }
    
    private function swap(&$a, &$b) {
        $temp = $a;
        $a = $b;
        $b = $temp;
    }

    public function getUsers(Request $request) {
        $rules = [
            'userType' => 'nullable|in:Alumnos,Docentes',
            'precedence' => 'nullable|in:Interino,Externo',
            'academy' => 'nullable|string',
            'career' => 'nullable|in:ISW,IIA,LCD',
            'curriculum' => 'nullable|date_format:Y|in:1999,2009,2020',
            'page' => 'required|int|min:1'
        ];

        $headers = [];
        foreach ($rules as $header => $rule){
            $headerValue = $request->header($header);
            if($headerValue !== null){
                $headers[$header] = is_array($headerValue) ? $headerValue[0] : $headerValue;
            }
        }

        $validator = Validator::make($headers, $rules);
        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $filters = collect($headers)->except('page')->all();
        $page = $headers['page'];

        $totalPages = 0;
        $usersResponse = [];
        $usersFound = [];

        // Get users based on filters
        if(!isset($filters['userType'])) {
            $usersFound = User::orderBy('created_at', 'desc')
            ->latest()
            ->skip(($page-1)*$this->wantedUsers)
            ->take($page*$this->wantedUsers)
            ->with('student')
            ->with('staff')
            ->get();
            $totalPages = ceil(User::count()/9);
        } elseif($filters['userType'] === "Docentes") {
            if(!array_key_exists("precedence", $filters)){
                $usersFound = User::orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$this->wantedUsers)
                ->take($page*$this->wantedUsers)
                ->whereHas('staff')
                ->with('staff')
                ->get();
                $totalPages = ceil(Staff::count()/9);
            } elseif(!array_key_exists("academy", $filters)){
                if($filters['precedence'] === "Interino"){
                    $usersFound = User::whereHas('staff', function ($query) use ($filters) {
                        $query->where('precedence', 'ESCOM');
                    })
                    ->orderBy('created_at', 'desc')
                    ->latest()
                    ->skip(($page-1)*$this->wantedUsers)
                    ->take($page*$this->wantedUsers)
                    ->with('staff')
                    ->get();
                    $totalPages = ceil(Staff::where('precedence', '!=', 'ESCOM')->count()/9);
                } elseif ($filters['precedence'] === "Externo") {
                    $usersFound = User::whereHas('staff', function ($query) use ($filters) {
                        $query->where('precedence', '!=' , 'ESCOM');
                    })
                    ->orderBy('created_at', 'desc')
                    ->latest()
                    ->skip(($page-1)*$this->wantedUsers)
                    ->take($page*$this->wantedUsers)
                    ->with('staff')
                    ->get();
                    $totalPages = ceil(Staff::where('precedence', '!=', 'ESCOM')->count()/9);
                }
            } else {
                $usersFound = User::whereHas('staff', function ($query) use ($filters) {
                    $query->where('precedence', 'ESCOM')
                          ->where('academy', $filters['academy']);
                })
                ->orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$this->wantedUsers)
                ->take($page*$this->wantedUsers)
                ->with('staff')
                ->get();
                $totalPages = ceil(Staff::where('precedence', '!=', 'ESCOM')->where('academy', $filters['academy'])->count()/9);
            }
            
        } elseif($filters['userType'] === "Alumnos") {
            if(!array_key_exists("career", $filters)){
                $usersFound = User::orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$this->wantedUsers)
                ->take($page*$this->wantedUsers)
                ->whereHas('student')
                ->with('student')
                ->get();
                $totalPages = ceil(Student::count()/9);
            } elseif(!array_key_exists("curriculum", $filters)){
                $usersFound = User::whereHas('student', function ($query) use ($filters) {
                    $query->where('career', $filters['career']);
                })
                ->orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$this->wantedUsers)
                ->take($page*$this->wantedUsers)
                ->with('student')
                ->get();
                $totalPages = ceil(Student::where('career', $filters['career'])->count()/9);
            } else {
                $usersFound = User::whereHas('student', function ($query) use ($filters) {
                    $query->where('career', $filters['career'])
                          ->where('curriculum', $filters['curriculum']);
                })
                ->orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page-1)*$this->wantedUsers)
                ->take($page*$this->wantedUsers)
                ->with('student')
                ->get();
                $totalPages = ceil(Student::where('career', $filters['career'])->where('curriculum', $filters['curriculum'])->count()/9);
            }
        }
        $usersResponse = $usersFound;

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
                unset($user['student']['gender']);
                unset($user['student']['student_id']);
                unset($user['student']['birth_date']);
                unset($user['student']['profile_image']);
                unset($user['student']['altern_email']);
                unset($user['student']['phone_number']);
                unset($user['student']['created_at']);
                unset($user['student']['updated_at']);
            } else {
                unset($user['student']);
                unset($user['staff']['id']);
                unset($user['staff']['user_id']);
                unset($user['staff']['gender']);
                unset($user['staff']['staff_id']);
                unset($user['staff']['birth_date']);
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
    public function deleteUser($id) {
        $user = User::find($id);

        if (!$user) {    
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Estudiante eliminado exitosamente'], 200);
    }
}