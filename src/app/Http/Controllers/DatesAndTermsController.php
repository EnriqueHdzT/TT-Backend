<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\DatesAndTerms;

class DatesAndTermsController extends Controller
{
    private $cycleRule = [
        'cycle' => ['required', 'string', 'regex:/^\d{4}\/[1-2]$/'],
    ];

    private $dateRule = [
        'status' => ['required', 'in:true,false'],
        'ord_start_update_protocols' => ['date'],
        'ord_end_update_protocols' => ['date'],
        'ord_start_sort_protocols' => ['date'],
        'ord_end_sort_protocols' => ['date'],
        'ord_start_eval_protocols' => ['date'],
        'ord_end_eval_protocols' => ['date'],
        'ord_start_change_protocols' => ['date'],
        'ord_end_change_protocols' => ['date'],
        'ord_start_second_eval_protocols' => ['date'],
        'ord_end_second_eval_protocols' => ['date'],
        'ext_start_update_protocols' => ['date'],
        'ext_end_update_protocols' => ['date'],
        'ext_start_sort_protocols' => ['date'],
        'ext_end_sort_protocols' => ['date'],
        'ext_start_eval_protocols' => ['date'],
        'ext_end_eval_protocols' => ['date'],
        'ext_start_change_protocols' => ['date'],
        'ext_end_change_protocols' => ['date'],
        'ext_start_second_eval_protocols' => ['date'],
        'ext_end_second_eval_protocols' => ['date'],
    ];

    private function isValidCycleFormat(array $data): bool
    {
        try {
            $validator = Validator::make($data, $this->cycleRule);
            return !$validator->fails();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function isValidDate(array $date): bool
    {
        try {
            $validator = Validator::make($date, $this->dateRule);
            return !$validator->fails();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function createSchoolCycle(Request $request)
    {
        try {
            $requestCycle = $request->only('cycle');
            if (!$this->isValidCycleFormat($requestCycle) || $request->keys() !== ['cycle']) {
                return response()->json(['error' => 'Error en la peticion'], 400);
            }

            if (DatesAndTerms::whereCycle($requestCycle['cycle'])->exists()) {
                return response()->json([], 200);
            }

            $newCycle = new DatesAndTerms([
                'cycle' => $requestCycle['cycle']
            ]);

            $newCycle->save();

            return response()->json([], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el servidor'], 500);
        }
    }

    public function getSchoolCycleData(Request $request)
    {
        try {
            if ($request->keys() !== ['cycle'] || !$this->isValidCycleFormat($request->only('cycle'))) {
                return response()->json(['error' => 'Error en la peticion'], 400);
            }

            if (!empty($request->keys())) {
                $schoolCycle = DatesAndTerms::where('cycle', $request->cycle)->first();
            } else {
                $schoolCycle = DatesAndTerms::orderByRaw("CAST(split_part(cycle, '/', 1) AS INTEGER) DESC")
                    ->orderByRaw("CAST(split_part(cycle, '/', 2) AS INTEGER) DESC")
                    ->first();
            }

            if (!$schoolCycle) {
                return response()->json(['error' => 'School cycle not found'], 404);
            }

            return response()->json($schoolCycle, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el servidor'], 500);
        }
    }

    public function getAllSchoolCycles()
    {
        try {
            $schoolCycles = DatesAndTerms::orderByRaw("CAST(split_part(cycle, '/', 1) AS INTEGER) DESC")
                ->orderByRaw("CAST(split_part(cycle, '/', 2) AS INTEGER) DESC")
                ->get('cycle');
            if (!$schoolCycles) {
                return response()->json([], 404);
            }
            return response()->json($schoolCycles, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el servidor'], 500);
        }
    }

    public function getAllSchoolCyclesAsArray()
    {
        try {
            $schoolCycles = DatesAndTerms::orderByRaw("CAST(split_part(cycle, '/', 1) AS INTEGER) DESC")
                ->orderByRaw("CAST(split_part(cycle, '/', 2) AS INTEGER) DESC")
                ->pluck('cycle')
                ->toArray();

            if (empty($schoolCycles)) {
                return response()->json([], 404);
            }

            return response()->json($schoolCycles, 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el servidor'], 500);
        }
    }

    public function deleteSchoolCycle(Request $request)
    {
        try {
            $requestCycle = $request->only('cycle');
            if (!$this->isValidCycleFormat($requestCycle)) {
                return response()->json(['error' => 'Error en la peticion'], 400);
            }

            $schoolCycle = DatesAndTerms::where('cycle', $requestCycle['cycle'])->first();
            if (!$schoolCycle) {
                return response()->json(['error' => 'School cycle not found'], 404);
            }
            $schoolCycle->delete();
            return response()->json([], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el servidor'], 500);
        }
    }

    public function updateSchoolCycle(Request $request)
    {
        try {
            $cycleData = $request->only('cycle');
            $datesData = $request->except('cycle');

            if (!$this->isValidCycleFormat($cycleData) || !$this->isValidDate($datesData)) {
                return response()->json(['error' => 'Invalid request data'], 400);
            }

            $schoolCycle = DatesAndTerms::where('cycle', $request->cycle)->first();
            if (!$schoolCycle) {
                return response()->json(['error' => 'School cycle not found'], 404);
            }

            $schoolCycle->update($datesData);
            return response()->json([], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el servidor'], 500);
        }
    }

    public function checkIfUploadIsAvailable()
    {
        try {
            $activeCycles = DatesAndTerms::where('status', true)->get();

            if ($activeCycles->isEmpty()) {
                return response()->json(['message' => 'No hay ciclos activos'], 404);
            }

            $activeCycles = $activeCycles->sortByDesc('cycle');
            $currentDate = Carbon::now();

            foreach ($activeCycles as $cycle) {
                $ordStart = Carbon::parse($cycle->ord_start_update_protocols);
                $ordEnd = Carbon::parse($cycle->ord_end_update_protocols);
                $extStart = Carbon::parse($cycle->ext_start_update_protocols);
                $extEnd = Carbon::parse($cycle->ext_end_update_protocols);

                if ($currentDate->between($ordStart, $ordEnd)) {
                    return response()->json(['type' => 'ord', 'cycle' => $cycle->cycle], 200);
                }

                if ($currentDate->between($extStart, $extEnd)) {
                    return response()->json(['type' => 'ext', 'cycle' => $cycle->cycle], 200);
                }
            }

            return response()->json(['message' => 'No hay ciclos activos'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error en el servidor'], 500);
        }
    }
}
