<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Staff;

class ProtocolController extends Controller
{
    public function createProtocol(Request $request)
    {
        $request->validate([
            'title_protocol' => 'required|string',
            'student_ID' => 'required|string|unique:students,student_ID',
            'staff_ID' => 'required|string|unique:staff,staff_ID',
            'keywords' => 'required|string',
            'protocol_doc' => 'required|binary',
        ]);
        

        return response()->json(['message' => 'Protocol creado exitosamente'], 201);
    }

    public function readProtocol($id)
    {
        $protocol= Protocol::find($id);

        if (!$protocol) {
            return response()->json(['message' => 'Protocolo no encontrado'], 404);
        }

        $protocolData = $protocol->toArray();
        unset($protocolData['protocol_id']);
        unset($protocolData['updated_at']);
        unset($protocolData['created_at']);

        return response()->json(['protocol' => $protocolData], 200);
    }

    public function readProtocols()
    {
        $protocols = Protocol::all();
        $formattedProtocols = [];

        foreach ($students as $protocol) {
            $protocolData = $protocol->toArray();
            unset($protocolData['protocol_id']);
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
            'student_ID' => 'required|string|unique:students,student_ID',
            'staff_ID' => 'required|string|unique:staff,staff_ID',
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

    public function deleteProtocol($id) {
        $protocol = Protocol::find($id);

        if (!$protocol) {    
            return response()->json(['message' => 'Protocolo no encontrado'], 404);
        }

        $user = User::find($protocol->protocol_id);
        $user->delete();
        $protocol->delete();
        return response()->json(['message' => 'Protocolo eliminado exitosamente'], 200);
    }

}
