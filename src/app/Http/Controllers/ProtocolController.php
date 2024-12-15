<?php

namespace App\Http\Controllers;

use App\Models\DatesAndTerms;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


use App\Models\User;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Protocol;
use App\Models\ProtocolRole;
use App\Models\ProtocolStatus;
use App\Services\FileService;
use Illuminate\Support\Facades\Auth;

class ProtocolController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function createProtocol(Request $request)
    {
        try {
            $request->merge([
                'students' => json_decode($request->input('students'), true),
                'directors' => json_decode($request->input('directors'), true),
                'sinodals' => json_decode($request->input('sinodals', '[]'), true),
                'keywords' => json_decode($request->input('keywords'), true),
            ]);

            // Define validation rules
            $rules = [
                'title' => 'required|string',
                'resume' => 'required|string',
                'students' => 'required|array|min:1|max:4',
                'students.*.email' => 'required|string|email|distinct',
                'directors' => 'required|array|min:1|max:2',
                'directors.*.email' => 'required|string|email|distinct',
                'sinodals' => 'array|min:0|max:3',
                'sinodals.*.email' => 'string|email|distinct',
                'term' => 'required|string',
                'keywords' => 'required|array|min:1|max:4',
                'pdf' => 'required|file|mimes:pdf|max:6144',
            ];

            $user = Auth::user();
            $isStudent = $user->student;
            $term = "";

            if ($isStudent) {
                unset($rules['sinodals'], $rules['term']);
                $request->replace($request->except(['term']));
                $request->replace($request->except(['sinodals']));

                $term = DatesAndTerms::latestActiveCycle();
                if ($term == '') {
                    return response()->json(['message' => 'No active term'], 400);
                } else {
                    $request->merge(['term' => $term]);
                }
            } elseif (!in_array($user->staff->staff_type, ['AnaCATT', 'SecEjec', 'SecTec', 'Presidente'])) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Validate the request body
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 422);
            }

            // Validate student don't have a protocol yet in this term
            foreach ($request->input('students', []) as $studentInput) {
                if (!isset($studentInput['email']) || empty($studentInput['email'])) {
                    return response()->json(['message' => 'Each student must have a valid email.'], 400);
                }

                $user = User::where('email', $studentInput['email'])->first();

                if ($user) {
                    $student = $user->student;
                    if ($student) {
                        $termId = DatesAndTerms::where('cycle', $request->input('term'))->firstOrFail()->id;
                        $protocol = $student->protocols()->where('period', $termId)->first();

                        if ($protocol) {
                            return response()->json(['message' => 'Student already has a protocol in this term'], 400);
                        }
                    }
                }
            }

            // Process participants
            $students = $this->processStudents($request->input('students'), $user, $isStudent);
            $directors = $this->processDirectors($request->input('directors'));
            $sinodals = [];
            if (!$isStudent) {
                $sinodalsInput = $request->input('sinodals');
                $sinodals = count($sinodalsInput) > 0 ? $this->processSinodals($sinodalsInput) : [];
            }

            // Create protocol
            $protocol = $this->createProtocolRecord($request, $students['ids'], $directors['ids'], $sinodals['ids'] ?? []);

            return response()->json(['protocol' => $protocol], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function processStudents($students, $user, $isStudent)
    {
        $studentIds = [];

        foreach ($students as &$student) {
            $existingStudent = User::where('email', $student['email'])->first();

            if ($existingStudent) {
                $studentIds[] = $existingStudent->id;
            } else {
                $newStudent = $this->createStudent($student);
                $studentIds[] = $newStudent->id;
            }
        }

        return ['ids' => $studentIds];
    }

    private function processDirectors($directors)
    {
        $directorIds = [];
        $firstDirector = User::where('email', $directors[0]['email'])->first();

        if (!$firstDirector) {
            throw new Exception("First director with email {$directors[0]['email']} must be registered.");
        }

        $directorIds[] = $firstDirector->id;

        if (isset($directors[1])) {
            $secondDirector = User::where('email', $directors[1]['email'])->first() ?: $this->createDirector($directors[1]);
            $directorIds[] = $secondDirector->id;
        }

        return ['ids' => $directorIds];
    }

    private function processSinodals($sinodals)
    {
        $sinodalIds = [];

        foreach ($sinodals as $sinodal) {
            $registeredSinodal = User::where('email', $sinodal['email'])->first();

            if (!$registeredSinodal) {
                throw new Exception("Sinodal with email {$sinodal['email']} not found.");
            }

            $sinodalIds[] = $registeredSinodal->id;
        }

        return ['ids' => $sinodalIds];
    }

    private function createProtocolRecord($request, $studentIds, $directorIds, $sinodalIds)
    {
        DB::beginTransaction();
        try {
            $protocol = new Protocol();
            $protocol->fill([
                'title' => $request->input('title'),
                'resume' => $request->input('resume'),
                'keywords' => json_encode($request->input('keywords'))
            ]);



            $datesAndTerms = DatesAndTerms::where('cycle', $request->input('term'))->firstOrFail();
            $protocol->period = $datesAndTerms->id;

            [$year, $term] = explode('/', $datesAndTerms->cycle);
            $year = (int)$year;
            $letter = ($term == '1') ? 'B' : ($year++ && 'A');

            $prefix = "{$year}-{$letter}";
            $maxProtocol  = Protocol::where('period', $datesAndTerms->id)
                ->where('protocol_id', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderBy('protocol_id', 'desc')
                ->first();

            $nextNumber = $maxProtocol
                ? (int)substr($maxProtocol->protocol_id, strlen($prefix)) + 1
                : 1;

            $protocol_id = sprintf('%s%03d', $prefix, $nextNumber);
            $pdf = $request->file('pdf');
            if ($pdf) {
                $protocol->fill([
                    'pdf' => $pdf->store("uploads/{$protocol_id}"),
                    'protocol_id' => $protocol_id
                ]);
            }
            $protocol->save();

            foreach ($studentIds as $studentId) {
                $newProtocolRole = new ProtocolRole();
                $newProtocolRole->protocol_id = $protocol->id;
                $newProtocolRole->user_id = $studentId;
                $newProtocolRole->role = 'student';
                $newProtocolRole->save();
            }
            foreach ($directorIds as $directorId) {
                $newProtocolRole = new ProtocolRole();
                $newProtocolRole->protocol_id = $protocol->id;
                $newProtocolRole->user_id = $directorId;
                $newProtocolRole->role = 'director';
                $newProtocolRole->save();
            }
            foreach ($sinodalIds as $sinodalId) {
                $newProtocolRole = new ProtocolRole();
                $newProtocolRole->protocol_id = $protocol->id;
                $newProtocolRole->user_id = $sinodalId;
                $newProtocolRole->role = 'sinodal';
                $newProtocolRole->save();
            }

            // if sinodals is empty array
            if (!empty($sinodalIds)) {
                $protocolStatus = new ProtocolStatus();
                $protocolStatus->protocol_id = $protocol->id;
                $protocolStatus->current_status = 'evaluatingFirst';
                $protocolStatus->comment = 'Protocolo creado con sinodales';
                $protocolStatus->save();
            } else {
                $protocolStatus = new ProtocolStatus();
                $protocolStatus->protocol_id = $protocol->id;
                $protocolStatus->comment = 'Protocolo creado sin sinodales';
                $protocolStatus->save();
            }


            DB::commit();

            return $protocol->id;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createStudent($student)
    {
        $user = User::create(['email' => $student['email'], 'password' => Hash::make(Str::random(12))]);
        return Student::create([
            'id' => $user->id,
            'name' => $student['name'] ?? null,
            'lastname' => $student['lastname'] ?? null,
            'second_lastname' => $student['second_lastname'] ?? null,
            'student_id' => $student['student_id'] ?? null,
            'career' => $student['career'] ?? null,
            'curriculum' => $student['curriculum'] ?? null,
        ]);
    }

    private function createDirector($director)
    {
        $user = User::create(['email' => $director['email'], 'password' => Hash::make(Str::random(12))]);
        $staff = Staff::create([
            'id' => $user->id,
            'name' => $director['name'] ?? null,
            'lastname' => $director['lastname'] ?? null,
            'second_lastname' => $director['second_lastname'] ?? null,
            'staff_type' => 'Prof',
            'precedence' => $director['precedence'] ?? null,
        ]);
        $staff->save();
        return $staff;
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

    public function deleteProtocol($id)
    {
        $protocol = Protocol::find($id);

        if (!$protocol) {
            return response()->json(['message' => 'Protocolo no encontrado'], 404);
        }

        $protocol->delete();
        return response()->json(['message' => 'Protocolo eliminado exitosamente'], 200);
    }

    public function getQuestionare()
    {
        try {
            $user = Auth::user();
            if (!$user->staff) {
                return response()->json(['message' => 'Acceso denegado'], 403);
            }
            return response()->json($this->fileService->getQuestionare(), 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error'], 500);
        }
    }

    public function allowedEvaluation($protocolId)
    {
        $user = Auth::user();

        $protocol = Protocol::find($protocolId);
        if (!$protocol) {
            return response()->json(['message' => 'Protocol not found'], 404);
        }

        $protocolStatus = $protocol->statusHistories[0];
        if (!in_array($protocolStatus->current_status, ['evaluatingFirst', 'correcting', 'evaluatingSecond', 'active', 'canceled'])) {
            return response()->json(['message' => 'Not allowed'], 403);
        }

        $protocolRole = $user->protocolRoles->where('protocol_id', $protocolId)->first();
        if (!$protocolRole) {
            return response()->json(['message' => 'Not allowed'], 403);
        }

        $permissions = match (true) {
            ($protocolRole->role === 'sinodal' && in_array($protocolStatus->current_status, ['evaluatingFirst', 'evaluatingSecond'])) => 'write',
            ($protocolRole->role === 'student' && in_array($protocolStatus->current_status, ['correcting', 'evaluatingSecond', 'canceled', 'active'])) => 'read',
            ($protocolRole->role === 'director' && in_array($protocolStatus->current_status, ['correcting', 'evaluatingSecond', 'canceled', 'active'])) => 'read',
            default => 'not allowed',
        };


        return response()->json(['permissions' => $permissions], 200);
    }

    public function getProtocolDoc($protocol_id)
    {
        $protocol = Protocol::where('protocol_id', $protocol_id)->first();

        if (!$protocol) {
            return response()->json(['message' => 'Error'], 404);
        }

        $protocolPath = $protocol->pdf; // descomentar cuando exista la columna

        $user = Auth::user();
        $isStudent = $user->student;
        $canAccess = false;

        if ($isStudent) {
            $protocols = $user->student->protocols;
            if ($this->checkIfExists($protocol_id, $protocols)) {
                $canAccess = true;
            }
        } else {
            $staff = $user->staff;
            switch ($staff->staff_type) {
                case 'PresAcad':
                case 'JefeDepAcad':
                case 'SecEjec':
                case 'SecTec':
                case 'Presidente':
                case 'AnaCATT':
                    $canAccess = true;
                    break;

                case 'Prof':
                    $protocols = $staff->protocols;
                    if ($this->checkIfExists($protocol_id, $protocols)) {
                        $canAccess = true;
                    }
                    break;
            }
        }

        if ($canAccess) {
            return $this->fileService->getFile($protocolPath);
        } else {
            return response()->json(
                ['message' => 'No tienes permiso para acceder a este recurso'],
                403
            );
        }
    }

    public function getProtocolDocByUUID(string $protocolId)
    {
        $protocol = Protocol::whereId($protocolId)->first();

        if (!$protocol) {
            return response()->json(['message' => 'Error'], 404);
        }

        $filePath = $protocol->pdf;

        $user = Auth::user();
        $canAccess = $user->protocolRoles()->where('protocol_id', $protocolId)->exists();
        if ($canAccess) {
            return $this->fileService->getFile($filePath);
        }

        return response()->json(['message' => 'No tienes permiso para acceder a este recurso'], 403);
    }

    public function listProtocols(Request $request)
    {
        $user = Auth::user();
        $elementsPerPage = 9;
        $isStudent = $user->student;
        $protocolsQuery = null;
        $cycle = $request->cycle;
        $page = $request->page ?? 1;
        $searchBar = $request->searchBar;
        $orderBy = $request->orderBy;

        if ($isStudent) {
            $protocolsQuery = $user->student->protocols();
            if ($cycle && $cycle != 'Todos') {
                $protocolsQuery->whereHas('datesAndTerms', function ($query) use ($cycle) {
                    $query->where('cycle', $cycle);
                });
            }
        } else {
            $staff = $user->staff;
            switch ($staff->staff_type) {
                case 'PresAcad':
                case 'JefeDepAcad':
                case 'SecEjec':
                case 'SecTec':
                case 'Presidente':
                case 'AnaCATT':
                case 'AnaCATT':
                    $protocolsQuery = Protocol::query();
                    if ($cycle && $cycle != 'Todos') {
                        $protocolsQuery->whereHas('datesAndTerms', function ($query) use ($cycle) {
                            $query->where('cycle', $cycle);
                        });
                    }
                    break;

                case 'Prof':
                    $protocolsQuery = $staff->protocols();
                    if ($cycle && $cycle != 'Todos') {
                        $protocolsQuery->whereHas('datesAndTerms', function ($query) use ($cycle) {
                            $query->where('cycle', $cycle);
                        });
                    }
                    break;
            }
        }

        if ($searchBar) {
            $protocolsQuery->where(function ($query) use ($searchBar) {
                $query->whereRaw('protocol_id ILIKE ?', ['%' . $searchBar . '%'])
                    ->orWhereRaw('title ILIKE ?', ['%' . $searchBar . '%']);
            });
        }

        if ($orderBy) {
            $protocolsQuery->orderByRaw("status = ? DESC", [$orderBy]);
        }

        $protocols = $protocolsQuery->paginate($elementsPerPage, ['*'], 'page', $page);

        return response()->json([
            'protocols' => $protocols->items(),
            'current_page' => $protocols->currentPage(),
            'total_pages' => $protocols->lastPage(),
        ], 200);
    }

    public function checkIfExists($protocol_id, $protocols)
    {
        foreach ($protocols as $protocol) {
            if ($protocol->protocol_id == $protocol_id) {
                return true;
            }
        }
        return false;
    }
}
