<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRenewall extends Model
{
    use HasFactory;
    protected $fillable = [
        'renewall_id',
        'responsible_id',
        'concept',
        'description',
        'method_payment',
        'reference',
        'value',
    ];

    public function renewall(): BelongsTo
    {
        return $this->belongsTo(Renewall::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
