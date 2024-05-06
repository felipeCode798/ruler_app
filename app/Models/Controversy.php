<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Controversy extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'appointment', 'code', 'subpoena','window', 'document_dni', 'document_power', 'document_status_account', 'state', 'category','processor_id', 'value_commission', 'value_received','total_value', 'value','observations','paid'];

    protected $casts = ['subpoena' => 'array'];

    protected $appends = ['payments_sum', 'payments_sum_processor', 'payments_sum_penalty'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function controversypayments(): HasMany
    {
        return $this->hasMany(ControversyPayments::class);
    }

    public function getPaymentsSumAttribute()
    {
        return $this->controversypayments()->sum('value');
    }

    public function suppliercontroversypayments(): HasMany
    {
        return $this->hasMany(SupplierControversyPayments::class);
    }

    public function getPaymentsSumSupplierAttribute()
    {
        return $this->suppliercontroversypayments()->sum('value');
    }

    public function processorcontroversypayments(): HasMany
    {
        return $this->hasMany(ProcessorControversyPayments::class);
    }

    public function getPaymentsSumProcessorAttribute()
    {
        return $this->processorcontroversypayments()->sum('value');
    }

    public function penaltycontroversypayments(): HasMany
    {
        return $this->hasMany(PenaltyPayments::class);
    }

    public function getPaymentsSumPenaltyAttribute()
    {
        return $this->penaltycontroversypayments()->sum('value');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


}
