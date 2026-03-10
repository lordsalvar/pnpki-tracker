<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'address_id',
        'office_id',
        'organizational_unit',
    ];
} 
