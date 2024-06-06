<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Accounting extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'total_revenue', 'total_expenses', 'grand_value', 'responsible_id'];

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function tramitadores(): HasMany
    {
        return $this->hasMany(AccountingDetail::class);
    }
}
