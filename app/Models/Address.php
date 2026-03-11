<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //
    protected $fillable = [
        'house_no',
        'street',
        'barangay',
        'municaplity',
        'province',
        'zip_code'
    ];

    

}
