<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\DatesAndTerms;

class DatesAndTermsController extends Controller
{
    public function createSchoolCycle(Request $request)
    {
        $rules = [
            'cycle' => 'required|string|regex:/^\d{4}\/[1-2]$',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['error' => 'Error en la peticion'], 400);
        }
        $newSchoolCycle = request('cycle');

        $newCycle = new DatesAndTerms();
        $newCycle->cycle = $newSchoolCycle;
        $newCycle->save();
        return response()->json([], 200);
    }

    public function readSchoolCycle($cycle)
    {
        $schoolCycle = DatesAndTerms::find($cycle);

        if (!$schoolCycle) {
            return response()->json(['error' => 'Ciclo no encontrado'], 404);
        }

        return response()->json($schoolCycle, 200);
    }

    public function getLatestSchoolCycle()
    {
        $latestSchoolCycle = DatesAndTerms::orderByRaw("SUBSTRING_INDEX(cycle, '/', 1) + 0 DESC")
                                          ->orderByRaw("SUBSTRING_INDEX(cycle, '/', -1) + 0 DESC")
                                          ->first();

        if (!$latestSchoolCycle) {
            return response()->json(['error' => 'Ciclo no encontrado'], 404);
        }

        return response()->json($latestSchoolCycle, 200);
    }
}
