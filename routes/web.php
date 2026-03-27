<?php

use App\Http\Controllers\SubmissionPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-psgc', function () {
    $raw = file_get_contents(storage_path('app/psgc/barangays.json'));
    $decoded = json_decode($raw, true);

    return response()->json([
        'first' => $decoded[0] ?? 'empty',
    ]);
});

Route::get('/submission/download-pdf', [SubmissionPdfController::class, 'download'])
    ->name('submission.download-pdf')
    ->middleware('signed');
