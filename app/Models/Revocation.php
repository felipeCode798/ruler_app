<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Revocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'processor_id',
        'responsible_id',
        'value_commission',
        'status_account',
        'grand_value',
        'status',
        'observations',
        'paid'
    ];

    protected $casts = [
        'status_account' => 'array'
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

    public function items(): HasMany
    {
        return $this->hasMany(RevocationProcess::class);
    }

    public function paymentprocess(): HasOne
    {
        return $this->hasOne(PaymentProcess::class);
    }

    public function categoryrevocation(): BelongsTo
    {
        return $this->belongsTo(CategoryRevocation::class);
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class);
    }

    public function processcategory(): BelongsTo
    {
        return $this->belongsTo(ProcessCategory::class);
    }
}
