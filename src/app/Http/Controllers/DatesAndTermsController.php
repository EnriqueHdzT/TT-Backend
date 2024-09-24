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

    private function isCycleRegexValid($data) {
        $validator = Validator::make($data, $this->cycleRule);
        return !$validator->fails();
    }

    public function createSchoolCycle(Request $request)
    {
        if(!$this->isCycleRegexValid($request->only('cycle')) || $request->keys() !== ['cycle']) {
            return response()->json(['error' => 'Error en la peticion'], 400);
        }
        if(DatesAndTerms::where('cycle', $request->cycle)->exists()){
            return response()->json([], 200);
        }

        $newCycle = new DatesAndTerms();
        $newCycle->cycle = request('cycle');
        $newCycle->save();
        return response()->json([], 200);
    }

    public function getSchoolCycle(Request $request)
    {
        if($request->keys() !== [] && $request->keys() !== ['cycle']){
            return response()->json([$request->keys()], 400);
        }
        if($request->keys() === []){
            $schoolCycle = DatesAndTerms::orderByRaw("CAST(split_part(cycle, '/', 1) AS INTEGER) DESC")
                                        ->orderByRaw("CAST(split_part(cycle, '/', 2) AS INTEGER) DESC")
                                        ->first();
        } else {
            if(!$this->isCycleRegexValid($request->only('cycle'))){
                return response()->json(['error' => 'Error en la peticion'], 400);
            }
            $schoolCycle = DatesAndTerms::where('cycle', $request->cycle)->first();
        }
        
        if (!$schoolCycle) {
            return response()->json([], 404);
        }

        return response()->json($schoolCycle, 200);
    }

    public function getAllSchoolCycles(){
        $schoolCycles = DatesAndTerms::orderByRaw("CAST(split_part(cycle, '/', 1) AS INTEGER) DESC")
                                    ->orderByRaw("CAST(split_part(cycle, '/', 2) AS INTEGER) DESC")
                                    ->get('cycle');
        if (!$schoolCycles) {
            return response()->json([], 404);
        }
        return response()->json($schoolCycles, 200);
    }
}
