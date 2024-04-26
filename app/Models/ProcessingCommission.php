<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessingCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'processor_id',
        'commission_controversy',
        'commission_course',
        'renewal_commission',
        'coercive_collection_commission',
        'commission_debit',
        'not_resolutions_commission',
        'payment_agreements_commission',
        'prescriptions_commission',
        'subpoena_commission',
        'license_commission',
        'pins_commission',
    ];
}
