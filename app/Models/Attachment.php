<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    //
    protected $fillable = [
        'employee_id',
        'file_type',
        'file_name',
        'file_path',
        'uploaded_at',
    ];

    public function employeeAttachment(): belongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
