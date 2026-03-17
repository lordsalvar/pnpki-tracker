<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function Employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
