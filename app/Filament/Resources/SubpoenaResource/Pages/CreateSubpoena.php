<?php

namespace App\Filament\Resources\SubpoenaResource\Pages;

use App\Filament\Resources\SubpoenaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubpoenaPending;
use App\Models\User;

class CreateSubpoena extends CreateRecord
{
    protected static string $resource = SubpoenaResource::class;
    protected static ?string $title = 'Crear Comparendo';

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
            'total_value' => $data['total_value'],
            'observations' => $data['observations'],
            'paid' => $data['paid'],
        );

        //Mail::to($client)->send(new SubpoenaPending($dataToSend));

        return $data;
    }
}
