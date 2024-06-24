<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pagos extends Model
{
    use HasFactory;

    protected $fillable = [
        'proceso_id',
        'registrar_proceso_id',
        'responsible_id',
        'concepto',
        'descripcion',
        'metodo_pago',
        'referencia',
        'valor'
    ];

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function registrarproceso(): BelongsTo
    {
        return $this->belongsTo(RegistrarProceso::class, 'registrar_proceso_id');
    }

    public function getRelatedProcessName()
    {
        $relatedProcess = $this->registrarproceso()->with('processCategory')->first();

        if ($relatedProcess && $relatedProcess->processCategory) {
            return $relatedProcess->processCategory->name;
        }

        return null;
    }
}
