<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    public function showForm()
    {
        return view('upload');
    }

    public function handleChunk(Request $request)
    {
        $file = $request->file('file');
        $chunkNumber = $request->input('chunkNumber');
        $totalChunks = $request->input('totalChunks');
        $originalName = $request->input('originalName');
        $uniqueIdentifier = $request->input('uniqueIdentifier');

        $tempPath = storage_path('app/temp/' . $uniqueIdentifier);
        $chunkPath = $tempPath . '/' . $chunkNumber;

        $file->move($tempPath, $chunkNumber);

        if ($chunkNumber == $totalChunks - 1) {
            $finalPath = storage_path('app/data/' . $originalName);
            $this->combineChunks($tempPath, $finalPath, $totalChunks);
            $this->cleanTemp($tempPath);
            return response()->json(['status' => 'completed', 'path' => $finalPath]);
        }

        return response()->json(['status' => 'chunk_uploaded']);
    }

    private function combineChunks($tempPath, $finalPath, $totalChunks)
    {
        $final = fopen($finalPath, 'wb');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunk = fopen($tempPath . '/' . $i, 'rb');
            stream_copy_to_stream($chunk, $final);
            fclose($chunk);
        }
        fclose($final);
    }

    private function cleanTemp($tempPath)
    {
        array_map('unlink', glob("$tempPath/*"));
        rmdir($tempPath);
    }
}