<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Percent extends Model
{
    use HasFactory;

    protected $fillable = [
        'coercive_collection',
        'debit',
        'not_resolutions',
        'payment_agreements',
        'prescriptions',
        'subpoena',
        'tabulated',
    ];

    public static function getCoerciveCollectionPercentage(): ?float
    {
        $percent = self::select('coercive_collection')->first();

        return $percent ? ($percent->coercive_collection / 100) : null;
    }

    public static function getDebitPercentage(): ?float
    {
        $percent = self::select('debit')->first();

        return $percent ? ($percent->debit / 100) : null;
    }

    public static function getPaymentAgreementsPercentage(): ?float
    {
        $percent = self::select('payment_agreements')->first();

        return $percent ? ($percent->payment_agreements / 100) : null;
    }

    public static function getPrescriptionsPercentage(): ?float
    {
        $percent = self::select('prescriptions')->first();

        return $percent ? ($percent->prescriptions / 100) : null;
    }

    public static function getSubpoenaPercentage(): ?float
    {
        $percent = self::select('subpoena')->first();

        return $percent ? ($percent->subpoena / 100) : null;
    }
}
