
<?php

use App\Http\Controllers\FileUploadController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

Route::get('/upload', [FileUploadController::class, 'showForm']);
Route::post('/upload-chunk', [FileUploadController::class, 'handleChunk'])->name('upload.chunk');