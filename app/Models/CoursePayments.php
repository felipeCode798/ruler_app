<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursePayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'course_id',
        'value',
    ];
}
