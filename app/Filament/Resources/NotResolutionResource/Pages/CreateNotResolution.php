<?php

namespace App\Filament\Resources\NotResolutionResource\Pages;

use App\Filament\Resources\NotResolutionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotResolutionsPending;
use App\Models\User;
use App\Models\NotResolutions as NotResolution;
use Carbon\Carbon;

class CreateNotResolution extends CreateRecord
{
    protected static string $resource = NotResolutionResource::class;
    protected static ?string $title = 'Sin resolución';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'];;
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'];
        $processor = User::find($processorId);

        $create_at = Carbon::parse(NotResolution::where('user_id', $clientUserId)->max('created_at'))->format('d/m/Y');

        $dataToSend = array(
            'client' => $client->name,
            'dni' => $client->dni,
            'email' => $client->email,
            'phone' => $client->phone,
            'invoice' => $client->dni.'-'.$clientUserId.'CTA',
            'subpoena' => $data['subpoena'],
            'cc' => $data['cc'],
            'category' => $data['category_id'],
            'state' => $data['state'],
            'value_received' => $data['value_received'],
            'value' => $data['value'],
            'processor_id' => $processor->name,
            'total_value' => $data['total_value'],
            'observations' => $data['observations'],
            'paid' => $data['paid'],
            'created_at' => $create_at,
        );

        Mail::to($client)->send(new NotResolutionsPending($dataToSend));

        return $data;
    }
}
