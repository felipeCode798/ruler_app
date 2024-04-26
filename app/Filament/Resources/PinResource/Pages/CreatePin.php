<?php

namespace App\Filament\Resources\PinResource\Pages;

use App\Filament\Resources\PinResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\PinsPending;
use App\Models\User;

class CreatePin extends CreateRecord
{
    protected static string $resource = PinResource::class;
    protected static ?string $title = 'Crear pin';

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
            'enlistment' => $data['enlistment'],
            'certificate' => $data['certificate'],
            'state' => $data['state'],
            'processor_id' => $processor->name,
            'value_commission' => $data['value_commission'],
            'total_value' => $data['total_value'],
            'observations' => $data['observations'],
        );

        //Mail::to($client)->send(new PinsPending($dataToSend));

        return $data;
    }
}
