<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\CoursePending;
use App\Models\User;
use App\Models\Course;
use Carbon\Carbon;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;
    protected static ?string $title = 'Crear Curso';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $clientUserId = $data['user_id'];
        $client = User::find($clientUserId);

        $processorId = $data['processor_id'];
        $processor = User::find($processorId);

        $create_at = Carbon::parse(Course::where('user_id', $clientUserId)->max('created_at'))->format('d/m/Y');

        $dataToSend = array(
            'client' => $client->name,
            'dni' => $client->dni,
            'email' => $client->email,
            'phone' => $client->phone,
            'invoice' => $client->dni.'-'.$clientUserId.'CTA',
            'state' => $data['state'],
            'subpoena' => $data['subpoena'],
            'value_cia' => $data['value_cia'],
            'value_transit' => $data['value_transit'],
            'processor_id' => $processor->name,
            'total_value' => $data['total_value'],
            'observations' => $data['observations'],
            'paid' => $data['paid'],
            'created_at' => $create_at,
        );

        Mail::to($client)->send(new CoursePending($dataToSend));

        return $data;
    }

}
