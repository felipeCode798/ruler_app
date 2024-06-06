<?php

namespace App\Filament\Resources\RevocationResource\Pages;

use App\Filament\Resources\RevocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRevocation extends EditRecord
{
    protected static string $resource = RevocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
