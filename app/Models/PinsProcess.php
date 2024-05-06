<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PinsProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'pines_asinged',
        'school_setup_id',
    ];


    public function schoolSetup(): BelongsTo
    {
        return $this->belongsTo(SchoolSetup::class);
    }

    public function getSchoolSetupIdAttribute()
    {
        return $this->schoolSetup->id;
    }
}
