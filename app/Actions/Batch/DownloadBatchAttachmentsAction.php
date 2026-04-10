<?php

namespace App\Actions\Batch;

use App\Models\Batch;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class DownloadBatchAttachmentsAction
{
    public function execute(Batch $batch): StreamedResponse
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $tempPath = tempnam(sys_get_temp_dir(), 'batch_');

        if ($tempPath === false) {
            throw new RuntimeException('Unable to create temporary archive file.');
        }

        $zip = new ZipArchive();

        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tempPath);

            throw new RuntimeException('Unable to create zip archive.');
        }

        $batch->load('formSubmissions.attachments');

        foreach ($batch->formSubmissions as $formSubmission) {
            $submissionFolder = trim($formSubmission->lastname . '-' . $formSubmission->firstname, '-');

            foreach ($formSubmission->attachments as $attachment) {
                $absolutePath = Storage::disk('local')->path($attachment->file_path);

                if (! file_exists($absolutePath)) {
                    continue;
                }

                $fileName = $attachment->file_name ?: basename($attachment->file_path);

                $zip->addFile($absolutePath, $submissionFolder . '/' . $fileName);
            }
        }

        $zip->close();

        return response()->streamDownload(function () use ($tempPath): void {
            try {
                readfile($tempPath);
            } finally {
                @unlink($tempPath);
            }
        }, $batch->batch_name . '.zip', [
            'Content-Type' => 'application/zip',
        ]);
    }
}