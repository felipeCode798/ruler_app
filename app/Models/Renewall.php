<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Renewall extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category', 'medical_exams', 'impression', 'value_exams', 'value_impression', 'state', 'observations','processor_id','value_commission', 'total_value'];

    protected $casts = [
        'category' => 'array',
    ];

    protected $appends = ['payments_sum'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function renewallpayments(): HasMany
    {
        return $this->hasMany(RenewallPayments::class);
    }

    public function getPaymentsSumAttribute()
    {
        return $this->renewallpayments()->sum('value');
    }
}
