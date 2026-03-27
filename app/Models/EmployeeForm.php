<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmployeeForm extends Model
{
    use HasUlids;

    protected $primaryKey = 'id';

    protected $table = 'employee_forms';

    protected static function booted(): void
    {
        static::creating(function (EmployeeForm $form): void {
            if (blank($form->public_id)) {
                $form->public_id = (string) Str::uuid();
            }

            if (auth()->check()) {
                if (blank($form->user_id)) {
                    $form->user_id = auth()->id();
                }
                if (blank($form->office_id)) {
                    $form->office_id = auth()->user()?->office_id;
                }
                if (blank($form->name)) {
                    $form->name = auth()->user()?->office?->name ?? 'Employee form';
                }
            } elseif (blank($form->name)) {
                $form->name = 'Employee form';
            }
        });

        static::created(function (EmployeeForm $form): void {
            EmployeeForm::query()
                ->where('office_id', $form->office_id)
                ->whereKeyNot($form->getKey())
                ->update(['is_active' => false]);
        });
    }

    protected $fillable = [
        'office_id',
        'user_id',
        'public_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Label for Filament breadcrumbs, headings, and record pickers (office acronym when available).
     */
    protected function recordLabel(): Attribute
    {
        return Attribute::get(function (): string {
            $acronym = $this->office?->acronym;

            return filled($acronym) ? $acronym : $this->name;
        });
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'form_id');
    }
}
