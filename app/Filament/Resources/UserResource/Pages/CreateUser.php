<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCreate;
use App\Models\User;


class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected static ?string $title = 'Crear Usuario';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $dataToSend = array(
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        );

        //Mail::to($data['email'])->send(new UserCreate($dataToSend));

        return $data;
    }
}
