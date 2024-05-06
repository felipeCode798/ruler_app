<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierRenewallPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'renewall_id',
        'user_id',
        'name',
        'value',
        'payment_method',
        'payment_reference',
    ];

    public function renewall()
    {
        return $this->belongsTo(Renewall::class);
    }
}
