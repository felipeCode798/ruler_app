<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterConfiguration extends Model
{
    use HasFactory;

    protected $fillable = ['filter_name', 'filter_category'];

    protected $casts = [
        'filter_category' => 'array',
    ];

}
