<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoerciveCollectionPayments extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'coercive_collection_id',
        'value',
    ];
}
