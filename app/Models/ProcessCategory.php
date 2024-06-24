<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use  Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'value_process'];

    public function revocation(): HasMany
    {
        return $this->hasMany(Revocation::class);
    }

    public function registrarProcesos(): HasMany
    {
        return $this->hasMany(RegistrarProceso::class);
    }
}
