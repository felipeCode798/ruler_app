<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LicensesSetupCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'price_exam',
        'price_slide',
        'school_letter',
        'price_fees',
        'price_no_course',
        'price_renewal_exam_client',
        'price_renewal_exam_slide_client',
        'price_renewal_exam_processor',
        'price_renewal_exam_slide_processor',
        'is_active'
    ];
}
