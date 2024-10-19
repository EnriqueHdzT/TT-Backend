<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\DatesAndTerms;

class DatesAndTermsController extends Controller
{
    private $cycleRule = [
        'cycle' => ['required', 'string', 'regex:/^\d{4}\/[1-2]$/'],
    ];

    private function isValidCycleFormat(array $data): bool
    {
        $validator = Validator::make($data, $this->cycleRule);
        return !$validator->fails();
    }

    public function createSchoolCycle(Request $request)
    {
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
    }

    public function getSchoolCycleData(Request $request)
    {
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
    }

    public function getAllSchoolCycles()
    {
        $schoolCycles = DatesAndTerms::orderByRaw("CAST(split_part(cycle, '/', 1) AS INTEGER) DESC")
            ->orderByRaw("CAST(split_part(cycle, '/', 2) AS INTEGER) DESC")
            ->get('cycle');
        if (!$schoolCycles) {
            return response()->json([], 404);
        }
        return response()->json($schoolCycles, 200);
    }

    public function checkIfUploadIsAvailable()
    {
        $activeCycles = DatesAndTerms::where('status', true)->get();

        if ($activeCycles->isEmpty()) {
            return response()->json(['message' => 'No hay ciclos activos'], 404);
        }

        $activeCycles = $activeCycles->sortByDesc('cycle');
        $currentDate = date('d-m-Y');

        foreach ($activeCycles as $cycle) {
            $ordStart = date('d-m-Y', strtotime($cycle->ord_start_update_protocols));
            $ordEnd = date('d-m-Y', strtotime($cycle->ord_end_update_protocols));
            $extStart = date('d-m-Y', strtotime($cycle->ext_start_update_protocols));
            $extEnd = date('d-m-Y', strtotime($cycle->ext_end_update_protocols));

            if (($currentDate >= $ordStart && $currentDate <= $ordEnd)) {
                return response()->json(['type' => 'ord', 'cycle' => $cycle->cycle], 200);
            }

            if (($currentDate >= $extStart && $currentDate <= $extEnd)) {
                return response()->json(['type' => 'ext', 'cycle' => $cycle->cycle], 200);
            }
        }

        return response()->json(['message' => 'No hay ciclos activos'], 404);
    }
}
