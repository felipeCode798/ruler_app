<?php

namespace App\Filament\Resources\ControversyResource\Pages;

use App\Filament\Resources\ControversyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\ControversyMail;

class CreateControversy extends CreateRecord
{
    protected static string $resource = ControversyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'] ?? null;
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'] ?? null;
        $processor = User::find($processorId);

        // Validar existencia de los datos requeridos
        if (!$client) {
            throw new \Exception("Cliente no encontrado");
        }

        $dataToSend = [
            'client' => $client->name ?? 'N/A',
            'dni' => $client->dni ?? 'N/A',
            'email' => $client->email ?? 'N/A',
            'phone' => $client->phone ?? 'N/A',
            'invoice' => ($client->dni ?? 'N/A').'-'.$clientUserId.'CTA',
            'state' => $data['status'] ?? 'N/A',
            'appointment' => $data['appointment'] ?? 'N/A',
            'code' => $data['code'] ?? 'N/A',
            'window' => $data['window'] ?? 'N/A',
            'total_value' => $data['grand_value'] ?? 0,
            'observations' => $data['observations'] ?? 'N/A',
            'paid' => $data['paid'] ?? false,
        ];

        Mail::to($client->email)->send(new ControversyMail($dataToSend));

        return $data;
    }
}
