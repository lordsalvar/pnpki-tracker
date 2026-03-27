<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasUlids;

    protected $primaryKey = 'id';

    protected $table = 'offices';

    protected $fillable = [
        'name',
        'acronym',
        'number_of_employees',
    ];

    protected $casts = [
        'number_of_employees' => 'integer',
    ];

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'office_id', 'id');
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
