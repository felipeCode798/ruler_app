<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotResolutionPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'not_resolution_id',
        'value',
    ];

}
