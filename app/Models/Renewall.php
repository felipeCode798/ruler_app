<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Renewall extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'medical_exams',
        'impression',
        'value_exams',
        'value_impression',
        'state',
        'observations',
        'document_status_account',
        'processor_id',
        'value_commission',
        'total_value',
        'responsible_id'
    ];

    protected $casts = [
        'category' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function paymentrenewall(): HasMany
    {
        return $this->hasMany(PaymentRenewall::class);
    }
}
