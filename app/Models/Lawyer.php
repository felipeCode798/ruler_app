<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\HasMany;

class Lawyer extends Model
{
    use HasFactory;

    protected $fillable = ['name','phone','prefix','commission','is_active','slug'];

    public function revocation(): HasMany
    {
        return $this->hasMany(Revocation::class);
    }
}
