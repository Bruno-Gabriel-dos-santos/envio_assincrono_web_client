<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'fileId' => 'required|string',
            'chunkIndex' => 'required|integer',
            'totalChunks' => 'required|integer',
            'fileName' => 'required|string',
            'fileSize' => 'required|integer'
        ]);

        $file = $request->file('file');
        $fileId = $request->input('fileId');
        $chunkIndex = $request->input('chunkIndex');
        $totalChunks = $request->input('totalChunks');
        
        // Caminho temporário
        $tempPath = storage_path("app/temp/{$fileId}");
        $chunkPath = "{$tempPath}/{$chunkIndex}";

        // Cria diretório se não existir
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0777, true);
        }

        // Move o chunk para o diretório temporário
        $file->move($tempPath, $chunkIndex);

        // Verifica se todos os chunks foram enviados
        $uploadedChunks = count(glob("{$tempPath}/*"));
        $progress = round(($uploadedChunks / $totalChunks) * 100, 2);

        if ($uploadedChunks === $totalChunks) {
            // Combina os chunks
            $finalPath = storage_path("app/data/{$request->input('fileName')}");
            $this->combineChunks($tempPath, $finalPath, $totalChunks);
            
            // Limpa os arquivos temporários
            Storage::deleteDirectory("temp/{$fileId}");

            return response()->json([
                'success' => true,
                'progress' => 100,
                'message' => 'Upload completo'
            ]);
        }

        return response()->json([
            'success' => true,
            'progress' => $progress,
            'message' => 'Chunk recebido'
        ]);
    }

    private function combineChunks($tempPath, $finalPath, $totalChunks)
    {
        $finalFile = fopen($finalPath, 'wb');

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkFile = fopen("{$tempPath}/{$i}", 'rb');
            stream_copy_to_stream($chunkFile, $finalFile);
            fclose($chunkFile);
        }

        fclose($finalFile);
    }
}
