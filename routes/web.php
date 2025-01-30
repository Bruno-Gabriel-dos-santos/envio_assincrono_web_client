<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;



use App\Http\Controllers\FileUploadController;

Route::get('/', function () {
    return view('i');
});

Route::post('/upload', FileUploadController::class);