<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'gender',
        'tin_number',
    ];
    //added eloquent relationships for address and office
    
    public function address():BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
 

    public function office():BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
    
    public function attachment():HasMany
    {
        return $this->hasMany(Attachment::class);
    }

} 
