<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    protected $table = 'form_submissions';

    protected $fillable = [
        'firstname',
        'lastname',
        'middlename',
        'suffix',
        'email',
        'phone_number',
        'batch_id',
        'address_id',
        'office_id',
        'form_id',
        'organizational_unit',
        'gender',
        'tin_number',
        'status',
    ];

    protected $casts = [
        'gender' => Gender::class,
    ];

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->firstname,
            $this->middlename,
            $this->lastname,
            $this->suffix,
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
