<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/download/{filename}/{filename2}', function ($filename, $filename2) {
    $file1 = Storage::disk('public')->path($filename);
    $file2 = Storage::disk('public')->path($filename2);

    if (file_exists($file1) && file_exists($file2)) {
        return response()->download($file1)->deleteFileAfterSend(true);
        // Aquí puedes devolver una respuesta para el segundo archivo si deseas descargar ambos.
    } else {
        abort(404);
    }
})->name('download');
