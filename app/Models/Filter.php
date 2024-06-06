<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\HasMany;

class Filter extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function revocation(): HasMany
    {
        return $this->hasMany(Revocation::class);
    }
}
