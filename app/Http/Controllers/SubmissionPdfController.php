<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Services\PsgcService;
use Illuminate\Http\Request;

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
        $genderLabel = $submission->gender instanceof \BackedEnum ? ucfirst($submission->gender->value) : ucfirst($submission->gender);

        // Resolve PSGC names from codes
        $psgc = app(PsgcService::class);
        $provinceName   = $psgc->provinces()[$submission->address->province]   ?? $submission->address->province;
        $municipalityName = $psgc->municipalities($submission->address->province)[$submission->address->municipality] ?? $submission->address->municipality;
        $barangayName   = $psgc->barangays($submission->address->municipality)[$submission->address->barangay]     ?? $submission->address->barangay;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.submission-receipt', [
            'submission'       => $submission,
            'provinceName'     => $provinceName,
            'municipalityName' => $municipalityName,
            'barangayName'     => $barangayName,
            'genderLabel'      => $genderLabel,

        ]);

        $filename = 'Submission_' . strtoupper($submission->lastname) . '_' . $submission->firstname . '.pdf';

        return $pdf->download($filename);
    }
}