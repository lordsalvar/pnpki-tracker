<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeForm extends Model
{
    protected $table = 'employee_forms';

    protected $fillable = [
        'office_id',
        'user_id',
        'public_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'form_id');
    }
}
