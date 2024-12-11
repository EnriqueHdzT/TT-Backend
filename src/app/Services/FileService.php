<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class FileService
{
    public function getFile($_filename)
    {
        // Clean the file name
        $filename = str_replace('../', '', $_filename);

        // Adjust this path to match the documents' location
        $path = storage_path('app/' . $filename);

        if (!File::exists($path)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        return Response::make($file, 200)->header("Content-Type", $type);
    }
}
