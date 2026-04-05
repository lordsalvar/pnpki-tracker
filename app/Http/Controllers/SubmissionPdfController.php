<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Services\PsgcService;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

class SubmissionPdfController extends Controller
{
    public function download(Request $request)
    {
        // Validate the signed URL is intact
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired download link.');
        }

        $submissionId = $request->query('submission_id');
        $submission = FormSubmission::with(['address', 'attachments'])->findOrFail($submissionId);
        $sexLabel = $submission->sex?->getLabel() ?? '—';

        // Resolve PSGC names from codes
        $psgc = app(PsgcService::class);
        $provinceName = $psgc->provinces()[$submission->address->province] ?? $submission->address->province;
        $municipalityName = $psgc->municipalities($submission->address->province)[$submission->address->municipality] ?? $submission->address->municipality;
        $barangayName = $psgc->barangays($submission->address->municipality)[$submission->address->barangay] ?? $submission->address->barangay;

        $filename = 'Submission_'.strtoupper($submission->lastname).'_'.$submission->firstname.'.pdf';

        return Pdf::view('pdf.submission-receipt', [
            'submission' => $submission,
            'provinceName' => $provinceName,
            'municipalityName' => $municipalityName,
            'barangayName' => $barangayName,
            'sexLabel' => $sexLabel,
        ])
            ->format('a4')
            ->name($filename)
            ->download();
    }
}
