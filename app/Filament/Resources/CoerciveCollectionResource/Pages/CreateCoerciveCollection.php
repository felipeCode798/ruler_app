<?php

namespace App\Filament\Resources\CoerciveCollectionResource\Pages;

use App\Filament\Resources\CoerciveCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\CoerciveCollectionPending;
use App\Models\User;
use App\Models\CoerciveCollection;
use Carbon\Carbon;

class CreateCoerciveCollection extends CreateRecord
{
    protected static string $resource = CoerciveCollectionResource::class;
    protected static ?string $title = 'Crear Cobro Coactivo';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'];;
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'];
        $processor = User::find($processorId);

        $create_at = Carbon::parse(CoerciveCollection::where('user_id', $clientUserId)->max('created_at'))->format('d/m/Y');

        $dataToSend = array(
            'client' => $client->name,
            'dni' => $client->dni,
            'email' => $client->email,
            'phone' => $client->phone,
            'invoice' => $client->dni.'-'.$clientUserId.'CTA',
            'cc' => $data['cc'],
            'subpoena' => $data['subpoena'],
            'state' => $data['state'],
            'value_received' => $data['value_received'],
            'value' => $data['value'],
            'processor_id' => $processor->name,
            'total_value' => $data['total_value'],
            'observations' => $data['observations'],
            'paid' => $data['paid'],
            'created_at' => $create_at,
        );

        Mail::to($client)->send(new CoerciveCollectionPending($dataToSend));

        return $data;

    }
}
