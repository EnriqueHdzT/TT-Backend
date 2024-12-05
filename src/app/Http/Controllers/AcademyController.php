<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

class AcademyController extends Controller
{

    
    
    function getAllAcademies()
    {
        $academies = DB::table('academies')->pluck('name');
        return response()->json($academies);
    }
    
}
