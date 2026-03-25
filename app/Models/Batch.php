<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\BatchStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasUlids;

    protected $primaryKey = 'id';

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
