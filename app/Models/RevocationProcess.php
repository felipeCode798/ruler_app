<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevocationProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'revocation_id',
        'categoryrevocation_id',
        'processcategory_id',
        'lawyer_id',
        'filter_id',
        'cc',
        'sa',
        'ap',
        'subpoena',
        'value_subpoema',
        'total_value_paymet',
        'status_subpoema',
        'status_account',
        'date_resolution',
    ];

    protected $casts = [
        'subpoena' => 'array',
    ];

    public function revocation(): BelongsTo
    {
        return $this->belongsTo(Revocation::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categoryrevocation(): BelongsTo
    {
        return $this->belongsTo(CategoryRevocation::class);
    }

    public function lawyer(): BelongsTo
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class);
    }

    public function processcategory(): BelongsTo
    {
        return $this->belongsTo(ProcessCategory::class);
    }

}
