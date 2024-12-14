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

    public function getQuestionare()
    {
        $filePath = storage_path('app/public/questionare.json');
        if (!File::exists($filePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        $file = File::get($filePath);
        $file = mb_convert_encoding($file, 'UTF-8', 'UTF-8');
        $jsonData = json_decode($file, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON format.'], 500);
        }

        return $jsonData;
    }
}
