<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;

class AttachmentPathService
{
    public function employeeDirectory(string $officeSlug, string $firstname, string $lastname): string
    {
        $first = str($firstname)->slug();
        $last = str($lastname)->slug();

        return "attachments/{$officeSlug}/Employees/{$last}-{$first}";
    }

    public function submissionDirectory(string $officeSlug, string $firstname, string $lastname, ?int $submissionId = null): string
    {
        $first = str($firstname)->slug();
        $last = str($lastname)->slug();
        $folder = "{$last}-{$first}";

        if ($submissionId !== null) {
            $folder = "{$submissionId}_{$folder}";
        }

        return "attachments/{$officeSlug}/FormSubmissions/{$folder}";
    }

    public function lastnameToken(string $lastname, string $fallback): string
    {
        $slug = str($lastname)->slug('_');

        return (string) str($slug === '' ? $fallback : $slug)->upper();
    }

    public function normalizeFilePath(mixed $value): ?string
    {
        foreach ($this->pathCandidates($value) as $candidate) {
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function pathCandidates(mixed $value): array
    {
        if (is_string($value)) {
            return [trim($value)];
        }

        if (! is_array($value)) {
            return [];
        }

        $candidates = [];

        foreach ($value as $key => $nestedValue) {
            if (is_string($key) && str_contains($key, '/')) {
                $candidates[] = trim($key);
            }

            $candidates = [...$candidates, ...$this->pathCandidates($nestedValue)];
        }

        return $candidates;
    }

    public function resolveStoredPath(?string $path, FilesystemAdapter $disk): ?string
    {
        if ($path === null) {
            return null;
        }

        if ($disk->exists($path)) {
            return $path;
        }

        if (! str_starts_with($path, 'attachments/')) {
            $prefixedPath = "attachments/{$path}";

            if ($disk->exists($prefixedPath)) {
                return $prefixedPath;
            }
        }

        return null;
    }

    public function findCanonicalPathForType(FilesystemAdapter $disk, string $targetDirectory, string $lastnameToken, string $fileType): ?string
    {
        if (! $disk->exists($targetDirectory)) {
            return null;
        }

        $expectedPrefix = "{$targetDirectory}/{$lastnameToken}_{$fileType}.";

        foreach ($disk->files($targetDirectory) as $storedFile) {
            if (str_starts_with($storedFile, $expectedPrefix)) {
                return $storedFile;
            }
        }

        return null;
    }

    public function moveToCanonicalPath(FilesystemAdapter $disk, string $sourcePath, string $targetDirectory, string $lastnameToken, string $fileType): string
    {
        $targetPath = $this->canonicalPath($sourcePath, $targetDirectory, $lastnameToken, $fileType);

        if ($sourcePath === $targetPath) {
            return $sourcePath;
        }

        if (! $disk->exists($sourcePath)) {
            return $sourcePath;
        }

        $disk->makeDirectory($targetDirectory);

        $contents = $disk->get($sourcePath);

        if ($contents === null) {
            return $sourcePath;
        }

        if (! $disk->put($targetPath, $contents)) {
            return $sourcePath;
        }

        $disk->delete($sourcePath);

        return $targetPath;
    }

    public function canonicalPath(string $sourcePath, string $targetDirectory, string $lastnameToken, string $fileType): string
    {
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $extension = $extension !== '' ? $extension : 'pdf';

        return "{$targetDirectory}/{$lastnameToken}_{$fileType}.{$extension}";
    }
}
