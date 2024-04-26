<?php

namespace App\Filament\Resources\RenewallResource\Pages;

use App\Filament\Resources\RenewallResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\RenewallPending;
use App\Models\User;

class CreateRenewall extends CreateRecord
{
    protected static string $resource = RenewallResource::class;
    protected static ?string $title = 'Crear Renovación';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'];;
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'];
        $processor = User::find($processorId);

        $dataToSend = array(
            'client' => $client->name,
            'email' => $client->email,
            'category' => $data['category'],
            'medical_exams' => $data['medical_exams'],
            'impression' => $data['impression'],
            'value_exams' => $data['value_exams'],
            'value_impression' => $data['value_impression'],
            'state' => $data['state'],
            'processor_id' => $processor->name,
            'total_value' => $data['total_value'],
            'observations' => $data['observations'],
        );

        //Mail::to($client)->send(new RenewallPending($dataToSend));

        return $data;
    }
}
