<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\BatchStatus;

class Batch extends Model
{
    //

    protected $fillable = [
        'office_id',
        'user_id',
        'batch_name',
        'status',
        'application_status',
    ];

    protected $casts = [
        'status' => BatchStatus::class,
        'application_status' => ApplicationStatus::class,
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
        return $this->hasMany(FormSubmission::class);
    }
}
