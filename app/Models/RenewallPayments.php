<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenewallPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'renewall_id',
        'value',
    ];
}
