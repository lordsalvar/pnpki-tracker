<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    //
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
    ];

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->firstname,
            $this->middlename,
            $this->lastname,
            $this->suffix,
        ])));
    }

    protected $casts = [
        'gender' => Gender::class,
    ];
    // added eloquent relationships for address and office

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

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function attachment(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
