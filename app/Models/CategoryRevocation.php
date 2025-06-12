<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryRevocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'smld_value',
        'subpoena_value',
        'cia_value_50',
        'transit_pay_50',
        'total_discount_50',
        'cia_value_20',
        'transit_pay_20',
        'total_discount_20',
        'standard_value',
        'is_active'
    ];

    public function revocation(): HasMany
    {
        return $this->hasMany(Revocation::class);
    }

    public function controversy(): HasMany
    {
        return $this->hasMany(Controversy::class);
    }

    public function course(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function registrarProcesos(): HasMany
    {
        return $this->hasMany(RegistrarProceso::class);
    }
}
