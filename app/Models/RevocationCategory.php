<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevocationCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'processor_percentage',
        'client_percentage',
        'observations',
        'is_active'
    ];
}
