<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasFactory;

    protected $table = 'offices';

    protected $fillable = [
        'name',
        'acronym',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'office_id', 'id');
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }

    public function setAcronymAttribute($value)
    {
        $this->attributes['acronym'] = strtoupper($value);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class, 'office_id', 'id');
    }
    //
}
