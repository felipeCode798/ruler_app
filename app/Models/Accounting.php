<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Licenses;
use App\Models\Pins;
use App\Models\Debit;
use App\Models\CoerciveCollection;
use App\Models\NotResolutions;
use App\Models\PaymentAgreement;
use App\Models\Prescription;
use App\Models\Subpoena;
use App\Models\Controversy;
use App\Models\Course;
use App\Models\Renewall;

class Accounting extends Model
{
    use HasFactory;

    protected $fillable = ['date_start', 'date_end', 'description', 'revenue', 'expenses', 'total_value'];


}
