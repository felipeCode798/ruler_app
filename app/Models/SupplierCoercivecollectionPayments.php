<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierCoercivecollectionPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'coercive_collection_id',
        'name',
        'value',
        'payment_method',
        'payment_reference',
    ];

    public function coercivecollection()
    {
        return $this->belongsTo(CoerciveCollection::class, 'coercive_collection_id');
    }
}
