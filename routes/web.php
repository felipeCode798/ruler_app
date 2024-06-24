<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProcesoController;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {return view('welcome');});

Route::get('procesos/{proceso}/pdf', [ProcesoController::class, 'descargarPDF'])->name('procesos.pdf');

Route::get('/estado-de-cuenta/{filename}', function ($filename) {
    $filePath = Storage::disk('public')->path($filename);

    if (file_exists($filePath)) {
        return response()->download($filePath)->deleteFileAfterSend(true);
    } else {
        abort(404);
    }
})->name('estado.de.cuenta.download');

Route::get('/downloadd/{filename}', function ($filename) {
    $file1 = Storage::disk('public')->path($filename);

    if (file_exists($file1)) {
        return response()->download($file1)->deleteFileAfterSend(true);
    } else {
        abort(404);
    }
})->name('downloadd');

Route::get('/download/{filename}/{filename2}', function ($filename, $filename2) {
    $file1 = Storage::disk('public')->path($filename);
    $file2 = Storage::disk('public')->path($filename2);

    if (file_exists($file1) && file_exists($file2)) {
        return response()->download($file1)->deleteFileAfterSend(true);
    } else {
        abort(404);
    }
})->name('download');


