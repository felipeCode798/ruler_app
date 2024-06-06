<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentControversy extends Model
{
    use HasFactory;

    protected $fillable = [
        'controversy_id',
        'responsible_id',
        'concept',
        'description',
        'method_payment',
        'reference',
        'value',
    ];

    public function controversy(): BelongsTo
    {
        return $this->belongsTo(Controversy::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
