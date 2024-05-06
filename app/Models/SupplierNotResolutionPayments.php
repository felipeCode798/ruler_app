<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierNotResolutionPayments extends Model
{
    use HasFactory;

    protected $fillable = ['not_resolution_id', 'name', 'value', 'payment_method', 'payment_reference'];

    public function notresolution(): BelongsTo
    {
        return $this->belongsTo(NotResolutions::class);
    }
}
