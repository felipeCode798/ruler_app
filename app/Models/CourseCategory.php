<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'transit_value_50',
        'processor_value_50',
        'client_value_50',
        'transit_value_25',
        'processor_value_25',
        'client_value_25',
        'is_active'
    ];
}
