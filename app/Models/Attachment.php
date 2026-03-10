<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
