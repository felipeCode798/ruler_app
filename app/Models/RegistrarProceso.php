<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class RegistrarProceso extends Model
{
    use HasFactory;

    protected $fillable = [
        'proceso_id',
        'processcategory_id',
        'simit',
        'categoryrevocation_id',
        'lawyer_id',
        'filter_id',
        'coursecategory_id',
        'pago_abogado',
        'pago_filtro',
        'categoria_licencias',
        'escuela',
        'enrrolamiento',
        'school',
        'valor_carta_escuela',
        'pin',
        'examen_medico',
        'impresion',
        'valor_examen',
        'valor_impresion',
        'sin_curso',
        'valor_sin_curso',
        'value_enlistment',
        'tipo_renovacion',
        'valor_renovacion',
        'comparendo',
        'valor_comparendo',
        'porcentaje_descuento',
        'descuento_50',
        'descuento_20',
        'descuento_25',
        'valor_cia',
        'valor_transito',
        'valor_tabulado',
        'valor_total_descuento',
        'valor',
        'codigo',
        'ventana',
        'cita',
        'date_resolution',
        'documento_dni',
        'documento_poder',
        'sa',
        'ap',
        'total_value_paymet',
        'status_subpoema',
        'pagado',
        'dni'
    ];

    protected $casts = [
        'categoria_licencias' => 'array',
        'comparendo' => 'array',
        'descuento_50' => 'boolean',
        'descuento_20' => 'boolean',
        'descuento_25' => 'boolean',
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

    public function proceso(): BelongsTo
    {
        return $this->belongsTo(Proceso::class);
    }

    public function processCategory(): BelongsTo
    {
        return $this->belongsTo(ProcessCategory::class, 'processcategory_id');
    }

    public function categoryRevocation(): BelongsTo
    {
        return $this->belongsTo(CategoryRevocation::class, 'categoryrevocation_id');
    }

    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'coursecategory_id');
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class, 'lawyer_id');
    }

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class, 'filter_id');
    }

    public function pagos(): HasOne
    {
        return $this->hasOne(Pagos::class);
    }

}

