<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControversyPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'controversy_id',
        'value',
    ];
}
