<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingDetail extends Model
{
    use HasFactory;

    protected $fillable = ['accounting_id','responsible','revenue','expenses','total_value'];

    public function accounting(): BelongsTo
    {
        return $this->belongsTo(Accounting::class);
    }

}
