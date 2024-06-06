<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'value_enlistment',
        'value_enlistment_payment',
        'pins_school_process',
        'total_pins',
        'state',
        'processor_id',
        'value_commission',
        'total_value',
        'observations',
    ];

    protected $casts = [
        'category' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function licensespayment(): HasOne
    {
        return $this->hasOne(LicensesPayment::class);
    }

    public function pinslicenses(): HasOne
    {
        return $this->hasOne(PinsLicenses::class);
    }

}
