<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

class ProcessReturn extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','type_process','process_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processData()
    {
        switch ($this->type_process) {
            case 'licenses':
                return Licenses::find($this->process_id);
            case 'pins':
                return Pins::find($this->process_id);
            case 'debits':
                return Debit::find($this->process_id);
            case 'coercive_collections':
                return CoerciveCollection::find($this->process_id);
            case 'not_resolutions':
                return NotResolutions::find($this->process_id);
            case 'payment_agreements':
                return PaymentAgreement::find($this->process_id);
            case 'prescriptions':
                return Prescription::find($this->process_id);
            case 'subpoenas':
                return Subpoena::find($this->process_id);
            case 'controversies':
                return Controversy::find($this->process_id);
            case 'courses':
                return Course::find($this->process_id);
            case 'renewalls':
                return Renewall::find($this->process_id);
            default:
                return null;
        }
    }

}
