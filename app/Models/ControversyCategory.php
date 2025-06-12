<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ControversyCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'processor_value',
        'client_value',
        'is_active'
    ];
}
