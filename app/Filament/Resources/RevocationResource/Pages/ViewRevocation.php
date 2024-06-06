<?php

namespace App\Filament\Resources\RevocationResource\Pages;

use App\Filament\Resources\RevocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRevocation extends ViewRecord
{
    protected static string $resource = RevocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
