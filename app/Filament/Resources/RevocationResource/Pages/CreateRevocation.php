<?php

namespace App\Filament\Resources\RevocationResource\Pages;

use App\Filament\Resources\RevocationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\RevocationMail;


class CreateRevocation extends CreateRecord
{
    protected static string $resource = RevocationResource::class;

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
            'processor_id' => $processor->name,
            'grand_value' => $data['grand_value'],
            'status' => $data['status'],
            'observations' => $data['observations'],
            'paid' => $data['paid'],
            //'items' => $items
        );

        Mail::to($client)->send(new RevocationMail($dataToSend));

        return $data;
    }
}
