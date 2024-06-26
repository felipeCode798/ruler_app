<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\CourseMail;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'];
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'];
        $processor = User::find($processorId);

        $dataToSend = array(
            'client' => $client->name  ?? 'N/A',
            'dni' => $client->dni  ?? 'N/A',
            'email' => $client->email  ?? 'N/A',
            'phone' => $client->phone  ?? 'N/A',
            'invoice' => $client->dni.'-'.$clientUserId.'CTA',
            'total_value' => $data['total_value'] ?? 0,
        );

        //Mail::to($client)->send(new CourseMail($dataToSend));

        return $data;
    }
}
