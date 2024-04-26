<?php

namespace App\Filament\Resources\ControversyResource\Pages;

use App\Filament\Resources\ControversyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\ControversyPending;
use App\Models\User;

class CreateControversy extends CreateRecord
{
    protected static string $resource = ControversyResource::class;
    protected static ?string $title = 'Crear Controversia';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'];;
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'];
        $processor = User::find($processorId);

        $dataToSend = array(
            'client' => $client->name,
            'dni' => $client->dni,
            'email' => $client->email,
            'phone' => $client->phone,
            'invoice' => $client->dni.'-'.$clientUserId.'CTA',
            'state' => $data['state'],
            'appointment' => $data['appointment'],
            'code' => $data['code'],
            'window' => $data['window'],
            'processor_id' => $processor->name,
            'total_value' => $data['total_value'],
            'observations' => $data['observations'],
            'paid' => $data['paid'],
        );

        Mail::to($client)->send(new ControversyPending($dataToSend));

        return $data;
    }
}
