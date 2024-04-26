<?php

namespace App\Filament\Resources\PrescriptionResource\Pages;

use App\Filament\Resources\PrescriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\PrescriptionPending;
use App\Models\User;

class CreatePrescription extends CreateRecord
{
    protected static string $resource = PrescriptionResource::class;
    protected static ?string $title = 'Crear Prescripción';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'];;
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'];
        $processor = User::find($processorId);

        $dataToSend = array(
            'client' => $client->name,
            'email' => $client->email,
            'sa' => $data['sa'],
            'cc' => $data['cc'],
            'subpoena' => $data['subpoena'],
            'state' => $data['state'],
            'value_received' => $data['value_received'],
            'value' => $data['value'],
            'processor_id' => $processor->name,
            'total_value' => $data['total_value'],
            'observations' => $data['observations'],
            'paid' => $data['paid'],
        );

        //Mail::to($client)->send(new PrescriptionPending($dataToSend));

        return $data;
    }
}
