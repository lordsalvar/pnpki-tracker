<?php

namespace App\Models;

use App\Enums\CivilStatus;
use App\Enums\FormSubmissionStatus;
use App\Enums\Sex;
use App\Services\FormSubmissionReferenceNumberGenerator;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Stored fields include public-registration-only data ({@see maiden_name}, birth place, {@see form_id})
 * that the admin form now surfaces; {@see reference_number} is assigned on create.
 */
class FormSubmission extends Model
{
    use HasUlids;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (FormSubmission $submission): void {
            if ($submission->reference_number !== null && $submission->reference_number !== '') {
                return;
            }

            $submission->reference_number = app(FormSubmissionReferenceNumberGenerator::class)->next();
        });
    }

    protected $primaryKey = 'id';

    protected $table = 'form_submissions';

    protected $fillable = [
        'firstname',
        'lastname',
        'middlename',
        'suffix',
        'maiden_name',
        'birth_date',
        'birth_place_country',
        'birth_place_province',
        'civil_status',
        'phone_number',
        'email',
        'batch_id',
        'address_id',
        'office_id',
        'form_id',
        'organization',
        'organizational_unit',
        'sex',
        'tin_number',
        'status',
        'flagged_by',
    ];

    protected $casts = [
        'sex' => Sex::class,
        'status' => FormSubmissionStatus::class,
        'flagged_by' => 'string',
        'birth_date' => 'date',
        'civil_status' => CivilStatus::class,
    ];

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->firstname,
            $this->middlename !== 'N/A' ? $this->middlename : null,
            $this->lastname,
            $this->suffix !== 'N/A' ? $this->suffix : null,
        ])));
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function employeeForm(): BelongsTo
    {
        return $this->belongsTo(EmployeeForm::class, 'form_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'form_submission_id');
    }
}
