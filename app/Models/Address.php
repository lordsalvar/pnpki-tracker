<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    //
    protected $fillable = [
        'house_no',
        'street',
        'barangay',
        'municipality',
        'province',
        'zip_code',
    ];

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
