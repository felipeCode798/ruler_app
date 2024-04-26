<?php

namespace App\Filament\Resources\PaymentAgreementResource\Pages;

use App\Filament\Resources\PaymentAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentAgreementPending;
use App\Models\User;

class CreatePaymentAgreement extends CreateRecord
{
    protected static string $resource = PaymentAgreementResource::class;
    protected static ?string $title = 'Crear acuerdo de pago';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'];;
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'];
        $processor = User::find($processorId);

        $dataToSend = array(
            'client' => $client->name,
            'email' => $client->email,
            'cc' => $data['cc'],
            'subpoena' => $data['subpoena'],
            'state' => $data['state'],
            'value_received' => $data['value_received'],
            'value' => $data['value'],
            'processor_id' => $processor->name,
            'value_commission' => $data['value_commission'],
            'total_value' => $data['total_value'],
            'observations' => $data['observations'],
            'paid' => $data['paid'],
        );

        //Mail::to($client)->send(new PaymentAgreementPending($dataToSend));

        return $data;
    }
}
