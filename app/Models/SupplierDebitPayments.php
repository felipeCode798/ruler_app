<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierDebitPayments extends Model
{
    use HasFactory;

    protected $fillable = ['debit_id', 'name', 'value', 'payment_method', 'payment_reference'];

    public function debit()
    {
        return $this->belongsTo(Debit::class);
    }
}
