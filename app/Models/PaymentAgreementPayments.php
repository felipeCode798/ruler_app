<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAgreementPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'payment_agreement_id',
        'value',
    ];
}
