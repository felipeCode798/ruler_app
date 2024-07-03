<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ComisionProcesos extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'controverisa',
        'curso',
        'renovacion',
        'cobro_coactivo',
        'adedudo',
        'sin_resolucion',
        'acuedo_pago',
        'prescripcion',
        'comparendo',
        'licencia',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
