<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoerciveCollection extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','cc', 'subpoena','value_received','value', 'document_status_account','state', 'processor_id', 'value_commission', 'total_value', 'observations','paid'];

    protected $casts = ['subpoena' => 'array'];

    protected $appends = ['payments_sum'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function coercivecollectionpayments(): HasMany
    {
        return $this->hasMany(CoerciveCollectionPayments::class);
    }

    public function getPaymentsSumAttribute()
    {
        return $this->coercivecollectionpayments()->sum('value');
    }

    public function suppliercoercivecollectionpayments(): HasMany
    {
        return $this->hasMany(SupplierCoercivecollectionPayments::class);
    }

    public function getSupplierPaymentsSumAttribute()
    {
        return $this->suppliercoercivecollectionpayments()->sum('value');
    }
}
