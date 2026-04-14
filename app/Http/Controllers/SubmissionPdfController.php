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

        $logoPath = public_path('images/ddslogo.png');
        $receiptLogoSrc = '';
        if (is_readable($logoPath)) {
            $mime = mime_content_type($logoPath) ?: 'image/png';
            $receiptLogoSrc = sprintf(
                'data:%s;base64,%s',
                $mime,
                base64_encode((string) file_get_contents($logoPath))
            );
        }

        $pdf = Pdf::view('pdf.submission-receipt', [
            'submission' => $submission,
            'provinceName' => $provinceName,
            'municipalityName' => $municipalityName,
            'barangayName' => $barangayName,
            'sexLabel' => $sexLabel,
            'receiptLogoSrc' => $receiptLogoSrc,
        ])
            ->format('a4')
            ->name($filename);

        if ($this->receiptPdfUsesChromiumFooter()) {
            $pdf
                ->margins(8, 8, 18, 8, 'mm')
                ->headerHtml(view('pdf.partials.receipt-chromium-header')->render())
                ->footerHtml(view('pdf.partials.receipt-chromium-footer')->render());
        }

        return $pdf->download();
    }

    /**
     * Chromium-based PDF engines substitute .pageNumber / .totalPages in footerTemplate only.
     * An explicit empty headerTemplate is required or Chrome prints date/title in the top margin.
     */
    private function receiptPdfUsesChromiumFooter(): bool
    {
        return in_array(
            (string) config('laravel-pdf.driver', 'cloudflare'),
            ['cloudflare', 'browsershot', 'gotenberg'],
            true
        );
    }
}
