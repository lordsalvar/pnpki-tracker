<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Office extends Model
{
    use HasFactory;
    
    protected $table = 'offices';

    protected $fillable = [
        'name',
        'acronym'
    ];
     public function employees()
    {
        return $this->hasMany(Employee::class, 'office_id', 'office_id');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function setAcronymAttribute($value)
    {
        $this->attributes['acronym'] = strtoupper($value);
    }
    //
}
