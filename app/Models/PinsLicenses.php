<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PinsLicenses extends Model
{
    use HasFactory;

    protected $fillable = [
        'licenses_id',
        'responsible_id',
        'school_setup_id',
        'pins_processes_id',
    ];

    public function licenses(): BelongsTo
    {
        return $this->belongsTo(Licenses::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function schoolsetup(): BelongsTo
    {
        return $this->belongsTo(SchoolSetup::class);
    }

    public function pinsprocesses() // Cambio de pinsProcess a pinsprocesses
    {
        return $this->hasMany(PinsProcess::class);
    }

}
