<?php

namespace App\Http\Controllers;

use App\Models\LetterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function downloadLetter(LetterRequest $letterRequest)
    {
        // Check if user can access this file
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Users can only download their own completed requests
        // Admins can download any completed request
        if (!$user->isAdmin() && $letterRequest->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        // Check if request has file
        if (empty($letterRequest->file_path)) {
            abort(404, 'File not found');
        }

        $filePath = storage_path('app/public/' . $letterRequest->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        // Create a proper filename for download
        if ($letterRequest->letter_number) {
            $baseName = str_replace('/', '-', $letterRequest->letter_number);
        } else {
            $baseName = 'Surat-' . $letterRequest->id;
        }

        $downloadFilename = $baseName . '.pdf';

        // Read file content
        $fileContent = file_get_contents($filePath);

        // Return response with proper headers for safe download
        return response($fileContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $downloadFilename . '"')
            ->header('Content-Length', strlen($fileContent))
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
