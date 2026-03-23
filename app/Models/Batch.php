<?php

namespace App\Models;

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
        'metadata',
    ];

    protected $casts = [
        'status' => BatchStatus::class,
        'metadata' => 'array',
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
