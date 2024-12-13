<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Student;
use App\Models\Staff;
use App\Models\Academy;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

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

    protected $wantedUsers = 8;

    public function searchUsers(Request $request)
    {
        $request->validate([
            'field' => 'required|string',
            'page' => 'required|int'
        ]);

        $field = trim($request->field);
        $page = $request->page;

        $totalPages = 1;
        $usersResponse["staff"] = [];
        $usersResponse["students"] = [];
        $uniqueUserIds = [];

        // If not spaces are found
        if (strpos($field, ' ') === false) {
            foreach ($this->columnsToDoSearchOnNoSpace as $table => $columns) {

                foreach ($columns as $column) {

                    if ($table === 'users') {
                        // Perform the search in the users table
                        $usersFound = DB::table($table)
                            ->where(function ($queryBuilder) use ($column, $field) {
                                $queryBuilder->where(DB::raw("LOWER(\"$column\")"), 'LIKE', strtolower($field) . '%');
                            })
                            ->get();

                        if (!empty($usersFound)) {
                            foreach ($usersFound as $user) {
                                if (!in_array($user->id, $uniqueUserIds)) {
                                    $uniqueUserIds[] = $user->id;
                                    $student = Student::where('id', $user->id)->first();
                                    $staff = Staff::where('id', $user->id)->first();
                                    $email = $user->email;

                                    if ($staff) {
                                        $user = $staff;
                                        $user->email = $email;
                                        unset($user->birth_date);
                                        unset($user->altern_email);
                                        unset($user->phone_number);
                                        unset($user->created_at);
                                        unset($user->updated_at);
                                        $usersResponse["staff"][] = $user;
                                    } else {
                                        $user = $student;
                                        $user->email = $email;
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
                            ->where(function ($queryBuilder) use ($column, $field) {
                                $queryBuilder->where(DB::raw("LOWER(\"$column\")"), 'LIKE', strtolower($field) . '%');
                            })
                            ->get();

                        if (!empty($usersFound)) {
                            foreach ($usersFound as $userFound) {
                                $user = User::where('id', $userFound->id)->first();
                                if (!in_array($user->id, $uniqueUserIds)) {
                                    $uniqueUserIds[] = $user->id;
                                    $userFound->email = $user->email;
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
        } else {
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
                            if (!in_array($user->id, $uniqueUserIds)) {
                                $uniqueUserIds[] = $user->id;
                                unset($user->id);
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
    private function generatePermutations($terms)
    {
        $result = [];
        $length = count($terms);
        $this->permute($terms, 0, $length - 1, $result);
        return $result;
    }

    private function permute(&$terms, $left, $right, &$result)
    {
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

    private function swap(&$a, &$b)
    {
        $temp = $a;
        $a = $b;
        $b = $temp;
    }



    public function VerifyMail($userId)
    {
        $user = User::find($userId);

        if ($user) {
            $user->email_is_verified = true;
            $user->save();
            // Redirigir a la página principal con un mensaje de éxito
            return redirect('http://localhost:5173/login')->with('message', 'Correo verificado correctamente.');
        } else {
            // Redirigir a la página principal con un mensaje de error
            return redirect('/')->with('error', 'Usuario no encontrado.');
        }
    }

    public function getUsers(Request $request)
    {
        $rules = [
            'userType' => 'nullable|in:Alumnos,Docentes',
            'precedence' => 'nullable|in:Interino,Externo',
            'academy' => 'nullable|string',
            'career' => 'nullable|in:ISW,IIA,LCD',
            'curriculum' => 'nullable|date_format:Y|in:2009,2020',
            'page' => 'required|int|min:1'
        ];

        $headers = [];
        foreach ($rules as $header => $rule) {
            $headerValue = $request->header($header);
            if ($headerValue !== null) {
                $headers[$header] = is_array($headerValue) ? $headerValue[0] : $headerValue;
            }
        }

        $validator = Validator::make($headers, $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $filters = collect($headers)->except('page')->all();
        $page = $headers['page'];

        $totalPages = 1;
        $usersResponse = [];
        $usersFound = [];

        // Get users based on filters
        if (!isset($filters['userType'])) {
            $usersFound = User::orderBy('created_at', 'desc')
                ->latest()
                ->skip(($page - 1) * $this->wantedUsers)
                ->take($page * $this->wantedUsers)
                ->with('student')
                ->with('staff')
                ->get();
            $totalPages = ceil(User::count() / $this->wantedUsers);
        } elseif ($filters['userType'] === "Docentes") {
            if (!array_key_exists("precedence", $filters)) {
                $usersFound = User::orderBy('created_at', 'desc')
                    ->latest()
                    ->skip(($page - 1) * $this->wantedUsers)
                    ->take($page * $this->wantedUsers)
                    ->whereHas('staff')
                    ->with('staff')
                    ->get();
                $totalPages = ceil(Staff::count() / $this->wantedUsers);
            } elseif (!array_key_exists("academy", $filters)) {
                if ($filters['precedence'] === "Interino") {
                    $usersFound = User::whereHas('staff', function ($query) use ($filters) {
                        $query->where('precedence', 'ESCOM');
                    })
                        ->orderBy('created_at', 'desc')
                        ->latest()
                        ->skip(($page - 1) * $this->wantedUsers)
                        ->take($page * $this->wantedUsers)
                        ->with('staff')
                        ->get();
                    $totalPages = ceil(Staff::where('precedence', '!=', 'ESCOM')->count() / $this->wantedUsers);
                } elseif ($filters['precedence'] === "Externo") {
                    $usersFound = User::whereHas('staff', function ($query) use ($filters) {
                        $query->where('precedence', '!=', 'ESCOM');
                    })
                        ->orderBy('created_at', 'desc')
                        ->latest()
                        ->skip(($page - 1) * $this->wantedUsers)
                        ->take($page * $this->wantedUsers)
                        ->with('staff')
                        ->get();
                    $totalPages = ceil(Staff::where('precedence', '!=', 'ESCOM')->count() / $this->wantedUsers);
                }
            } else {
                $usersFound = User::whereHas('staff', function ($query) use ($filters) {
                    $query->where('precedence', 'ESCOM')
                        ->whereHas('academies', function ($academyQuery) use ($filters) {
                            $academyQuery->where('name', $filters['academy']);
                        });
                })
                    ->orderBy('created_at', 'desc')
                    ->latest()
                    ->skip(($page - 1) * $this->wantedUsers)
                    ->take($page * $this->wantedUsers)
                    ->with(['staff.academies'])
                    ->get();

                $totalPages = ceil(
                    User::whereHas('staff', function ($query) use ($filters) {
                        $query->where('precedence', 'ESCOM')
                            ->whereHas('academies', function ($academyQuery) use ($filters) {
                                $academyQuery->where('name', $filters['academy']);
                            });
                    })->count() / $this->wantedUsers
                );
            }
        } elseif ($filters['userType'] === "Alumnos") {
            if (!array_key_exists("career", $filters)) {

                $usersFound = User::orderBy('created_at', 'desc')
                    ->latest()
                    ->skip(($page - 1) * $this->wantedUsers)
                    ->take($page * $this->wantedUsers)
                    ->whereHas('student')
                    ->with('student')
                    ->get();
                $totalPages = ceil(Student::count() / $this->wantedUsers);
            } elseif (!array_key_exists("curriculum", $filters)) {
                $usersFound = User::whereHas('student', function ($query) use ($filters) {
                    $query->where('career', $filters['career']);
                })
                    ->orderBy('created_at', 'desc')
                    ->latest()
                    ->skip(($page - 1) * $this->wantedUsers)
                    ->take($page * $this->wantedUsers)
                    ->with('student')
                    ->get();
                $totalPages = ceil(Student::where('career', $filters['career'])->count() / $this->wantedUsers);
            } else {
                $usersFound = User::whereHas('student', function ($query) use ($filters) {
                    $query->where('career', $filters['career'])
                        ->where('curriculum', $filters['curriculum']);
                })
                    ->orderBy('created_at', 'desc')
                    ->latest()
                    ->skip(($page - 1) * $this->wantedUsers)
                    ->take($page * $this->wantedUsers)
                    ->with('student')
                    ->get();
                $totalPages = ceil(Student::where('career', $filters['career'])->where('curriculum', $filters['curriculum'])->count() / $this->wantedUsers);
            }
        }
        $usersResponse = $usersFound;

        // Validate if users where found
        if (count($usersResponse) === 0) {
            return response()->json(['message' => 'Usuarios no encontrados'], 404);
        }

        $replyData = [];
        foreach ($usersResponse as &$user) {
            $newUser = [];

            $newUser['id'] = $user->id;
            $newUser['email'] = $user->email;

            // Check if student exists and add their data
            if ($user->student !== null) {
                $newUser['student']['name'] = $user->student->name;
                $newUser['student']['lastname'] = $user->student->lastname;
                if ($user->student->second_lastname !== null) {
                    $newUser['student']['second_lastname'] = $user->student->second_lastname;
                }
                $newUser['student']['student_id'] = $user->student->student_id;
                $newUser['student']['career'] = $user->student->career;
                $newUser['student']['curriculum'] = $user->student->curriculum;
            } elseif ($user->staff !== null) {  // Ensure $user->staff is also not null
                $newUser['staff']['name'] = $user->staff->name;
                $newUser['staff']['lastname'] = $user->staff->lastname;
                if ($user->staff->second_lastname !== null) {
                    $newUser['staff']['second_lastname'] = $user->staff->second_lastname;
                }
                $newUser['staff']['precedence'] = $user->staff->precedence;
                $newUser['staff']['academies'] = $user->staff->academies->pluck('name')->toArray();
            }

            // Add the new user to the response array
            $replyData[] = (object) $newUser;
        }

        // Add numPages to the response array
        $replyData[] = ['numPages' => $totalPages];

        // Return the modified data
        return $replyData;
    }

    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'Usuario eliminado exitosamente'], 200);
    }

    public function getSelfId()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'No se pudo obtener el ID del usuario'], 404);
        }
        return response()->json(['id' => $user->id], 200);
    }

    public function getUserData($id)
    {
        $authUser = Auth::user();
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userInfo = [];
        $privilegedRoles = ['AnaCATT', 'SecEjec', 'SecTec', 'Presidente'];

        // Check if the authenticated user has privileged roles and retrieve the user's data accordingly
        if ($authUser->staff && in_array($authUser->staff->staff_type, $privilegedRoles)) {
            if ($user->student) {
                $userInfo = $user->student->toArray();
                $userInfo['user_type'] = 'student';
            } elseif ($user->staff) {
                $userInfo = $user->staff->toArray();
                $userInfo['user_type'] = 'staff';

                // Add the academies if they exist
                $academies = $user->staff->academies()->pluck('name')->toArray();
                if (!empty($academies)) {
                    $userInfo['academies'] = $academies;
                }
            }
        } else {
            // If the authenticated user is a student or staff member but not privileged
            if ($authUser->student) {
                $userInfo = $authUser->student->toArray();
                $userInfo['user_type'] = 'student';
            } else {
                $userInfo = $authUser->staff->toArray();
                $userInfo['user_type'] = 'staff';

                // Add the academies if they exist
                $academies = $authUser->staff->academies()->pluck('name')->toArray();
                if (!empty($academies)) {
                    $userInfo['academies'] = $academies;
                }
            }
        }

        // Remove unnecessary fields from the response
        unset($userInfo['id'], $userInfo['created_at'], $userInfo['updated_at']);
        $userInfo['email'] = $user->email;

        return response()->json($userInfo, 200);
    }


    public function createStudent(Request $request)
    {
        $rules = [
            'email' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@alumno\.ipn\.mx$/',
            'name' => 'required|string',
            'lastName' => 'required|string',
            'secondLastName' => 'string',
            'boleta' => 'required|string|size:10',
            'career' => 'required|in:ISW,IIA,LCD',
            'curriculum' => 'required|in:2009,2020',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['message' => 'Los datos no cumplen con la estructura no esperada'], 422);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            return response()->json(['message' => 'Ya existe un usuario con este correo'], 422);
        }
        $user = new User();
        $user->email = $request->email;
        $user->password = Hash::make(Str::random(12));
        $user->save();

        $student = new Student();
        $student->id = $user->id;
        $student->name = $request->name;
        $student->lastname = $request->lastName;
        $request->secondLastName == null ? $student->second_lastname = null : $student->second_lastname = $request->secondLastName;
        $student->student_id = $request->boleta;
        $student->career = $request->career;
        $student->curriculum = $request->curriculum;
        $student->save();


        return response()->json(['message' => 'Estudiante creado exitosamente'], 200);
    }

    public function createStaff(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@ipn\.mx$/',
            ],
            'name' => 'required|string',
            'lastName' => 'required|string',
            'secondLastName' => 'nullable|string',
            'precedence' => 'required|string',
            'academy' => 'array|min:1',
            'academy.*' => 'string',
            'userType' => 'required|in:Prof,AnaCATT,SecEjec,SecTec,Presidente',
        ]);

        // Check if the user already exists
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            return response()->json(['message' => 'Ya existe un usuario con este correo'], 422);
        }

        // Create the user
        $user = new User();
        $user->email = $request->email;
        $user->password = Hash::make(Str::random(12));
        $user->save();

        if (!$user) {
            return response()->json(['message' => 'Error al crear el usuario'], 500);
        }

        // Create the staff record
        $staff = new Staff();
        $staff->id = $user->id;
        $staff->name = $request->name;
        $staff->lastname = $request->lastName;
        $request->secondLastName == null ? $staff->second_lastname = null : $staff->second_lastname = $request->secondLastName;
        $staff->precedence = $request->precedence;
        $staff->staff_type = $request->userType;
        $staff->save();

        if (!$staff) {
            return response()->json(['message' => 'Error al crear el profesor'], 500);
        }

        foreach ($request->academy as $academyName) {
            $academy = Academy::where('name', $academyName)->first();
            if ($academy) {
                $staff->academies()->attach($academy->id);
            } else {
                return response()->json(['message' => 'Academia no encontrada'], 404);
            }
        }

        return response()->json(['message' => 'Profesor creado exitosamente'], 200);
    }

    public function updateUserData(Request $request)
    {
        try {
            $user = User::find($request->id);

            if (!$user) {
                return response()->json(['message' => 'Usuario no encontrado'], 404);
            }

            $currentUser = Auth::user();
            $isAuthorizedStaff = $currentUser->staff
                && in_array($currentUser->staff->staff_type, [
                    'SecEjec',
                    'SecTec',
                    'Presidente',
                    'AnaCATT',
                ]);

            if ($currentUser->id === $user->id || $isAuthorizedStaff) {
                $user->email = $request->email ?? $user->email;

                if ($user->staff) {
                    $staff = $user->staff;

                    // Update basic staff data
                    $staff->fill([
                        'name' => $request->name ?? $staff->name,
                        'lastname' => $request->lastName ?? $staff->lastname,
                        'second_lastname' => $request->secondLastName ?? $staff->second_lastname,
                        'precedence' => $request->precedence ?? $staff->precedence,
                        'staff_type' => $request->userType ?? $staff->staff_type,
                        'altern_email' => $request->alternEmail ?? $staff->altern_email,
                        'phone_number' => $request->phoneNumber ?? $staff->phone_number,
                    ]);
                    $staff->save();

                    if (isset($request->academy) && is_array($request->academy)) {
                        $currentAcademies = $staff->academies->pluck('name')->toArray();
                        $academiesToRemove = array_diff($currentAcademies, $request->academy);

                        foreach ($academiesToRemove as $academyName) {
                            $academy = Academy::where('name', $academyName)->first();
                            if ($academy) {
                                $staff->academies()->detach($academy->id);
                            }
                        }

                        // Add new academies that are not currently related
                        $academiesToAdd = array_diff($request->academy, $currentAcademies);

                        foreach ($academiesToAdd as $academyName) {
                            $academy = Academy::where('name', $academyName)->first();
                            if ($academy) {
                                $staff->academies()->attach($academy->id);
                            } else {
                                return response()->json(['message' => 'Academia no encontrada'], 404);
                            }
                        }
                    }
                } else {
                    $student = $user->student;
                    $student->fill([
                        'name' => $request->name ?? $student->name,
                        'lastname' => $request->lastName ?? $student->lastname,
                        'second_lastname' => $request->secondLastName ?? $student->second_lastname,
                        'student_id' => $request->studentId ?? $student->student_id,
                        'career' => $request->career ?? $student->career,
                        'curriculum' => $request->curriculum ?? $student->curriculum,
                        'phone_number' => $request->phoneNumber ?? $student->phone_number,
                        'altern_email' => $request->alternEmail ?? $student->altern_email,
                    ]);
                    $student->save();
                }

                return response()->json(['message' => 'Datos actualizados exitosamente'], 200);
            }

            return response()->json(['message' => 'No tienes permiso para actualizar estos datos'], 403);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar los datos'], 500);
        }
    }


    public function doesUserExists($email)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            $name = "";
            $lastName = "";
            $secondLastName = "";
            if ($user->staff) {
                $name = $user->staff->name;
                $lastName = $user->staff->lastname;
                $secondLastName = $user->staff->second_lastname;
            } else if ($user->student) {
                $name = $user->student->name;
                $lastName = $user->student->lastname;
                $secondLastName = $user->student->second_lastname;
            }
            return response()->json(["name" => $name, "lastName" => $lastName, "secondLastName" => $secondLastName], 200);
        }
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    public function getSelfEmail()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $userEmail = $user->email;
        $userName = $user->staff ? $user->staff->name : $user->student->name;
        $userLastName = $user->staff ? $user->staff->lastname : $user->student->lastname;
        $userSecondLastName = $user->staff ? $user->staff->second_lastname : $user->student->second_lastname;

        $response = [
            'email' => $userEmail,
            'name' => $userName,
            'lastName' => $userLastName
        ];

        if ($userSecondLastName) {
            $response['secondLastName'] = $userSecondLastName;
        }

        return response()->json($response, 200);
    }
}
