<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSetup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_school',
        'address',
        'phone',
        'responsible',
        'total_pins',
    ];

    public function pinsProcesses()
    {
        return $this->hasMany(PinsProcess::class);
    }
}
