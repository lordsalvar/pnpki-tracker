<?php

namespace App\Services;

use App\Models\FormSubmission;
use Illuminate\Support\Facades\DB;

class FormSubmissionReferenceNumberGenerator
{
    private const string PREFIX = 'PNPKI';

    private const int SEQUENCE_LENGTH = 7;

    /**
     * Format a reference number: PNPKI-{year}-{zero-padded sequence}.
     */
    public static function format(int $year, int $sequence): string
    {
        $suffix = str_pad((string) $sequence, self::SEQUENCE_LENGTH, '0', STR_PAD_LEFT);

        return self::PREFIX.'-'.$year.'-'.$suffix;
    }

    /**
     * Reserve the next unique reference number for the given calendar year (defaults to current year).
     */
    public function next(?int $year = null): string
    {
        $year = $year ?? (int) now()->year;

        if ($year < 2000 || $year > 2100) {
            throw new \InvalidArgumentException('Reference year must be between 2000 and 2100.');
        }

        return DB::transaction(function () use ($year): string {
            DB::table('form_submission_reference_sequences')->insertOrIgnore([
                'year' => $year,
                'last_sequence' => 0,
            ]);

            $row = DB::table('form_submission_reference_sequences')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                throw new \RuntimeException('Failed to lock reference sequence for year '.$year.'.');
            }

            $next = max((int) $row->last_sequence, $this->maxSequenceFromExistingSubmissions($year)) + 1;

            DB::table('form_submission_reference_sequences')
                ->where('year', $year)
                ->update(['last_sequence' => $next]);

            return self::format($year, $next);
        });
    }

    private function maxSequenceFromExistingSubmissions(int $year): int
    {
        $prefix = self::PREFIX.'-'.$year.'-';

        $last = FormSubmission::query()
            ->where('reference_number', 'like', $prefix.'%')
            ->orderByDesc('reference_number')
            ->value('reference_number');

        if ($last === null || ! is_string($last)) {
            return 0;
        }

        if (preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last, $matches) !== 1) {
            return 0;
        }

        return (int) $matches[1];
    }
}
