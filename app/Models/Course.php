<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'categoryrevocation_id',
        'subpoena',
        'value_cia',
        'value_transit',
        'state',
        'document_status_account',
        'processor_id',
        'value_commission',
        'total_value',
        'observations',
        'paid',
        'responsible_id'
    ];

    protected $casts = ['subpoena' => 'array'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processor_id');
    }

    public function categoryrevocation(): BelongsTo
    {
        return $this->belongsTo(CategoryRevocation::class);
    }

    public function paymentcourse(): HasOne
    {
        return $this->hasOne(PaymentCourse::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
