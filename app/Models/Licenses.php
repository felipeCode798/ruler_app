<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Licenses extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'school',
        'enlistment',
        'medical_exams',
        'impression',
        'value_exams',
        'value_impression',
        'state',
        'processor_id',
        'value_commission',
        'total_value',
        'observations',
    ];

    protected $casts = [
        'category' => 'array',
    ];

    protected $appends = ['payments_sum'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payments::class);
    }

    public function getPaymentsSumAttribute()
    {
        return $this->payments()->sum('value');
    }

}
