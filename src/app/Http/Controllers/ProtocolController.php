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
use App\Models\Evaluation;
use App\Services\FileService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
                // if folder contains any pdf file, delete it
                Storage::deleteDirectory("uploads/{$protocol_id}");
                $protocol->pdf = $pdf->store("uploads/{$protocol_id}");
            }

            $protocol->protocol_id = $protocol_id;
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

    public function getProtocolData(string $id)
    {
        $user = Auth::user();

        try {
            $protocol = Protocol::findOrFail($id);
            $term = DatesAndTerms::findOrFail($protocol->period);

            $protocolData = [
                'title' => $protocol->title,
                'resume' => $protocol->resume,
                'students' => [],
                'directors' => [],
                'sinodals' => [],
                'term' => $term->cycle,
                'keywords' => json_decode($protocol->keywords) ?? [],
            ];

            foreach ($protocol->students as $student) {
                $protocolData['students'][] = [
                    'email' => $student->person->email,
                    'name' => $student->person->student->name,
                    'lastname' => $student->person->student->lastname,
                    'second_lastname' => $student->person->student->second_lastname ?? null,
                ];
            }

            foreach ($protocol->directors as $director) {
                $protocolData['directors'][] = [
                    'email' => $director->person->email,
                    'name' => $director->person->staff->name,
                    'lastname' => $director->person->staff->lastname,
                    'second_lastname' => $director->person->staff->second_lastname ?? null,
                ];
            }

            foreach ($protocol->sinodals as $sinodal) {
                $protocolData['sinodals'][] = [
                    'email' => $sinodal->person->email,
                    'name' => $sinodal->person->staff->name,
                    'lastname' => $sinodal->person->staff->lastname,
                    'second_lastname' => $sinodal->person->staff->second_lastname ?? null,
                ];
            }

            return response()->json($protocolData, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Protocolo no encontrado'], 404);
        }
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
        try {
            if (empty($request->all())) {
                return response()->json(['message' => 'No se proporcionaron datos para actualizar'], 400);
            }
            $protocol = Protocol::findOrFail($id);

            $request->merge([
                'students' => json_decode($request->input('students', '[]'), true),
                'directors' => json_decode($request->input('directors', '[]'), true),
                'sinodals' => json_decode($request->input('sinodals', '[]'), true),
                'keywords' => json_decode($request->input('keywords', '[]'), true),
            ]);

            // Validate updated fields
            $rules = [
                'title' => 'string',
                'resume' => 'string',
                'students' => 'array|min:0|max:4',
                'students.*.email' => 'string|email|distinct',
                'directors' => 'array|min:0|max:2',
                'directors.*.email' => 'string|email|distinct',
                'sinodals' => 'array|min:0|max:3',
                'sinodals.*.email' => 'string|email|distinct',
                'keywords' => 'array|min:0|max:4',
                'term' => 'string',
                'pdf' => 'file|mimes:pdf|max:6144',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 422);
            }

            // Update protocol fields if present in the request
            if ($request->has('title')) {
                $protocol->title = $request->title;
            }
            if ($request->has('resume')) {
                $protocol->resume = $request->resume;
            }
            if ($request->has('keywords') && is_array($request->keywords) && count($request->keywords) > 0) {
                $protocol->keywords = json_encode($request->keywords);
            }
            if ($request->hasFile('pdf')) {
                if ($protocol->pdf) {
                    Storage::delete($protocol->pdf);
                }
                $protocol->pdf = $request->file('pdf')->store("uploads/{$protocol->protocol_id}");
            }

            if ($request->has('term')) {
                $newTerm = DatesAndTerms::where('cycle', $request->term)->first();
                if (!$newTerm) {
                    return response()->json(['message' => 'El periodo ingresado no es válido'], 400);
                }
                $protocol->period = $newTerm->id;
            }

            if ($request->has('students') && is_array($request->students) && count($request->students) > 0) {
                $this->syncParticipants($protocol, 'students', $request->input('students', []));
            }
            if ($request->has('directors') && is_array($request->directors) && count($request->directors) > 0) {
                $this->syncParticipants($protocol, 'directors', $request->input('directors', []));
            }
            if ($request->has('sinodals') && is_array($request->sinodals) && count($request->sinodals) > 0) {
                $this->syncParticipants($protocol, 'sinodals', $request->input('sinodals', []));
            }

            $protocol->save();

            return response()->json(['message' => 'Protocolo actualizado exitosamente'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Protocolo no encontrado'], 404);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function syncParticipants(Protocol $protocol, string $relation, array $participants)
    {
        $existingParticipants = $protocol->{$relation};
        $existingIds = $existingParticipants->pluck('user_id')->toArray();

        $newEmails = collect($participants)->pluck('email')->toArray();
        $newUsers = User::whereIn('email', $newEmails)->get();
        $newIds = $newUsers->pluck('id')->toArray();

        $toRemove = array_diff($existingIds, $newIds);
        $toAdd = array_diff($newIds, $existingIds);

        foreach ($toRemove as $userId) {
            $protocol->{$relation}()->where('user_id', $userId)->delete();
        }

        // Add new participants
        foreach ($toAdd as $userId) {
            $protocol->{$relation}()->create(['user_id' => $userId]);
        }
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
        $canAccess = false;
        $protocol = Protocol::whereId($protocolId)->first();

        if (!$protocol) {
            return response()->json(['message' => 'Error'], 404);
        }

        $filePath = $protocol->pdf;

        $user = Auth::user();
        $protocolRole = $user->protocolRoles->where('protocol_id', $protocolId);
        if ($protocolRole[0]) {
            $canAccess = true;
        }

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

    public function getDataForEvaluation($id)
    {
        $canAccess = false;
        $protocol = Protocol::whereId($id)->first();

        if (!$protocol) {
            return response()->json(['message' => 'Error'], 404);
        }

        $user = Auth::user();

        $protocolRole = $user->protocolRoles->where('protocol_id', $id);
        if ($protocolRole[0]) {
            $canAccess = true;
        }

        if ($canAccess) {
            $reply = [
                'protocol_id' => $protocol->protocol_id,
            ];
            return response()->json($reply, 200);
        }

        return response()->json(['message' => 'No tienes permiso para acceder a este recurso'], 403);
    }

    public function evaluateProtocol(Request $request, $id)
    {
        $user = Auth::user();

        $protocol = Protocol::whereId($id)->first();
        $protocolStatus = ProtocolStatus::where('protocol_id', $id)->first();
        $protocolRole = $user->protocolRoles->where('protocol_id', $id)->where('role', 'sinodal')->first();
        if (!$protocolRole && ($protocolStatus->current_status != 'evaluatingFirst' || $protocolStatus->current_status != 'evaluatingSecond')) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $evaluation = $protocol->evaluations->where('user_id', $user->id)->first();
        $evalResult = $request->input('Aprobado')['validation'];

        if (!$evaluation || $evaluation->current_status == 'pending') {
            $newEvaluation = new Evaluation();
            $newEvaluation->protocol_id = $id;
            $newEvaluation->sinodal_id = $user->id;
            $newEvaluation->evaluation_response = $request->json()->all();
            $newEvaluation->current_status = $evalResult ? 'approved' : 'rejected';
            $newEvaluation->save();
        } else {
            $evaluation->evaluation_response = $request->json()->all();
            $evaluation->current_status = $evalResult ? 'approved' : 'rejected';
            $evaluation->save();
        }

        // Check if there are three evaluations and all from different sinodals
        $evaluations = $protocol->evaluations;
        $evaluations = $evaluations->push($newEvaluation);
        $uniqueSinodals = $evaluations->pluck('sinodal_id')->unique();

        if ($uniqueSinodals->count() == 3) {
            $allApproved = $evaluations->every(function ($evaluation) {
                return $evaluation->current_status === 'approved';
            });

            $protocolStatus->previous_status = $protocolStatus->current_status;

            if ($allApproved) {
                $protocolStatus->current_status = 'active';
            } else {
                if ($protocolStatus->current_status === 'evaluatingFirst') {
                    $protocolStatus->current_status = 'correcting';
                } elseif ($protocolStatus->current_status === 'evaluatingSecond') {
                    $protocolStatus->current_status = 'canceled';
                }
            }

            $protocolStatus->save();
        }

        return response()->json(['message' => 'Protocolo evaluado correctamente'], 200);
    }


    public function getProtocolEvaluation(Request $request)
    {
        $protocolId = $request->input('id');
        $sinodalId = $request->input('sinodal_id');
        $evaluation = Evaluation::where('protocol_id', $protocolId)->where('sinodal_id', $sinodalId)->first();
        if (!$evaluation) {
            return response()->json(['message' => 'No se encontró la evaluación'], 404);
        }

        return response()->json($evaluation->evaluation_response, 200);
    }
}
