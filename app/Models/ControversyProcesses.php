<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControversyProcesses extends Model
{
    use HasFactory;

    protected $fillable = [
        'controversy_id',
        'categoryrevocation_id',
        'subpoena',
        'total_value',
        'value',
    ];

    protected $casts = [
        'subpoena' => 'array',
    ];

    public function controversy(): BelongsTo
    {
        return $this->belongsTo(Controversy::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categoryrevocation(): BelongsTo
    {
        return $this->belongsTo(CategoryRevocation::class);
    }


}
