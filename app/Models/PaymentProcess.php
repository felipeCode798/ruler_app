<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'revocation_id',
        'responsible_id',
        'concept',
        'description',
        'method_payment',
        'reference',
        'value',
    ];

    public function licenses(): BelongsTo
    {
        return $this->belongsTo(Licenses::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
