<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DatesAndTerms;

class DatesAndTermsController extends Controller
{
    public function createSchoolCycle()
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $newSchoolCycle = "";
        // Check latest school cycle registered in the database and create follow one
        $latestSchoolCycleInDB = DatesAndTerms::orderByRaw("SUBSTRING_INDEX(cycle, '-', 1) + 0 DESC") // Order by year descending
                                            ->orderByRaw("SUBSTRING_INDEX(cycle, '-', -1) + 0 DESC") // Then by term descending
                                            ->first();
        if (!$latestSchoolCycleInDB) {
            if($currentMonth < 7){
                $newSchoolCycle = $currentYear . "-1";
            } else {
                $newSchoolCycle = $currentYear . "-2";
            }
        }
        else {
            $newLatestSchoolCycle = explode("-", $latestSchoolCycleInDB->cycle);
            if($newLatestSchoolCycle[1] == 2){
                $newSchoolCycle = $newLatestSchoolCycle[0] + 1 . "-1";
            } else {
                $newSchoolCycle = $newLatestSchoolCycle[0] . "-2";
            }
        }
        
        if($newSchoolCycle == ""){
            return response()->json(['error' => 'Error al crear nuevo ciclo'], 500);
        }

        $newCycle = New DatesAndTerms();
        $newCycle->cycle = $newSchoolCycle;
        $newCycle->save();
        return response()->json(['cycle' => $newSchoolCycle], 200);
    }
}
