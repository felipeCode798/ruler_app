<?php

namespace App\Filament\Resources\LicenseResource\Pages;

use App\Filament\Resources\LicenseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\LicensesPending;
use App\Models\User;
use App\Models\License;
use Carbon\Carbon;

class CreateLicense extends CreateRecord
{
    protected static string $resource = LicenseResource::class;
    protected static ?string $title = 'Crear Licencia';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'];;
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'];
        $processor = User::find($processorId);

        $create_at = Carbon::parse(License::where('user_id', $clientUserId)->max('created_at'))->format('d/m/Y');

        $dataToSend = array(
            'client' => $client->name,
            'dni' => $client->dni,
            'email' => $client->email,
            'phone' => $client->phone,
            'invoice' => $client->dni.'-'.$clientUserId.'CTA',
            'category' => $data['category'],
            'school' => $data['school'],
            'enlistment' => $data['enlistment'],
            'medical_exams' => $data['medical_exams'],
            'impression' => $data['impression'],
            'value_exams' => $data['value_exams'],
            'value_impression' => $data['value_impression'],
            'state' => $data['state'],
            'processor_id' => $processor->name,
            'total_value' => $data['total_value'],
            'observations' => $data['observations'],
            'created_at' => $create_at,
        );

        Mail::to($client)->send(new LicensesPending($dataToSend));

        return $data;
    }
}
