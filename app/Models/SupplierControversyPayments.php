<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierControversyPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'controversy_id',
        'user_id',
        'name',
        'value',
        'payment_method',
        'payment_reference',
    ];

    public function controversy()
    {
        return $this->belongsTo(SupplierControversy::class, 'controversy_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
