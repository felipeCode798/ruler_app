<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryRevocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'comparing_value',
        'comparing_value_discount',
        'fee_value',
        'transit_value',
        'cia_value',
        'cia_discount_value',
        'cia_total_value',
        'process_value',
        'is_active',
        'price',
        'slug'
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

}
