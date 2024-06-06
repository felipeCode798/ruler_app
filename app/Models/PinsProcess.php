<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use  Illuminate\Database\Eloquent\Relations\HasMany;

class PinsProcess extends Model
{
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

    public function pinslicenses()
    {
        return $this->hasMany(PinsLicenses::class);
    }
}
