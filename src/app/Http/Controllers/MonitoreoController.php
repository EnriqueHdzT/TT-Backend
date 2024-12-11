<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Monitoreo;

class MonitoreoController extends Controller
{
    public function getMonitoreo($id)
    {
        $monitoreo = Monitoreo::find($id);

        if (!$monitoreo) {
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }

        return response()->json($monitoreo);
    }
}
