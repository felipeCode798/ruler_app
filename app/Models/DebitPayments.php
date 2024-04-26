<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'debit_id',
        'value',
    ];
}
