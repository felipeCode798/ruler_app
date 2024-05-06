<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierCoursePayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'user_id',
        'name',
        'value',
        'payment_method',
        'payment_reference',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

}
