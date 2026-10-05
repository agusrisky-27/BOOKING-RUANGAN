<?php

namespace App\Http\Controllers;

use App\Models\BookingAttachment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function download(BookingAttachment $attachment): StreamedResponse|Response
    {
        $bookingRequest = $attachment->bookingRequest;

        // Policy authorization check (NFR-04, P7)
        Gate::authorize('downloadAttachment', $bookingRequest);

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'Berkas lampiran tidak ditemukan pada server.');
        }

        return Storage::disk('local')->download($attachment->file_path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
        ]);
    }
}
