<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'category_id','subpoena', 'value_cia', 'value_transit', 'state', 'document_status_account', 'processor_id', 'value_commission', 'total_value', 'observations', 'paid'];

    protected $casts = ['subpoena' => 'array'];

    protected $appends = ['payments_sum'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function coursepayments(): HasMany
    {
        return $this->hasMany(CoursePayments::class);
    }

    public function getPaymentsSumAttribute()
    {
        return $this->coursepayments()->sum('value');
    }

    public function suppliercoursepayments(): HasMany
    {
        return $this->hasMany(SupplierCoursePayments::class);
    }

    public function getPaymentsSumSupplierAttribute()
    {
        return $this->suppliercoursepayments()->sum('value');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
