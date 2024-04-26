<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debit extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'sa', 'cc', 'subpoena','value_received','value','state', 'processor_id', 'value_commission', 'total_value', 'observations','paid'];

    protected $appends = ['payments_sum'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function debitpayments(): HasMany
    {
        return $this->hasMany(DebitPayments::class);
    }

    public function getPaymentsSumAttribute()
    {
        return $this->debitpayments()->sum('value');
    }
}
