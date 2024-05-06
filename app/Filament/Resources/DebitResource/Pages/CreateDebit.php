<?php

namespace App\Filament\Resources\DebitResource\Pages;

use App\Filament\Resources\DebitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\DebitPending;
use App\Models\User;
use App\Models\Debit;
use Carbon\Carbon;

class CreateDebit extends CreateRecord
{
    protected static string $resource = DebitResource::class;
    protected static ?string $title = 'Crear adeudo';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'];;
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'];
        $processor = User::find($processorId);

        $create_at = Carbon::parse(Debit::where('user_id', $clientUserId)->max('created_at'))->format('d/m/Y');

        $dataToSend = array(
            'client' => $client->name,
            'dni' => $client->dni,
            'email' => $client->email,
            'phone' => $client->phone,
            'invoice' => $client->dni.'-'.$clientUserId.'CTA',
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
            'created_at' => $create_at,
        );

        Mail::to($client)->send(new DebitPending($dataToSend));

        return $data;
    }
}
