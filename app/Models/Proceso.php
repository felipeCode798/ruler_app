<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;


class Proceso extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'processor_id',
        'responsible_id',
        'valor_comision',
        'estado_cuenta',
        'value_received',
        'porcentaje_descuento',
        'gran_total',
        'estado',
        'observacion',
        'pagado',
        'gestion'
    ];

    protected $casts = [
        'estado_cuenta' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function registrarProcesos(): HasMany
    {
        return $this->hasMany(RegistrarProceso::class);
    }

    public function proceso(): HasMany
    {
        return $this->hasMany(RegistrarProceso::class);
    }

    public function processCategory(): BelongsTo
    {
        return $this->belongsTo(ProcessCategory::class, 'processcategory_id');
    }

    public function pagos(): HasOne
    {
        return $this->hasOne(Pagos::class);
    }

    public function getRelatedProcessCategories()
    {
        return DB::table('process_categories')
            ->join('registrar_procesos', 'process_categories.id', '=', 'registrar_procesos.processcategory_id')
            ->join('procesos', 'registrar_procesos.proceso_id', '=', 'procesos.id')
            ->where('procesos.id', $this->id)
            ->select('registrar_procesos.id', 'process_categories.name')
            ->get();
    }

    public function getRelatedProcessName()
    {
        $relatedProcess = $this->registrarProcesos()->first();

        if ($relatedProcess) {
            return $relatedProcess->processCategory->name;
        }

        return null;
    }

    public function getTotalPagadoAttribute()
    {
        return $this->pagos()->sum('valor');
    }


}
