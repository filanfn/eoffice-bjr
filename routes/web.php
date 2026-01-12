<?php

use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Download route for letter requests (requires auth)
Route::get('/download/letter/{letterRequest}', [DownloadController::class, 'downloadLetter'])
    ->name('download.letter')
    ->middleware('auth');
