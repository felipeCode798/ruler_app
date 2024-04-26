<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessorControversyPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'controversy_id',
        'processor_id',
        'value',
    ];
}
