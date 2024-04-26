<?php

namespace App\Filament\Resources\LicenseResource\Pages;

use App\Filament\Resources\LicenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLicenses extends ListRecords
{
    protected static string $resource = LicenseResource::class;
    protected static ?string $title = 'Licencias';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Crear Licencia'),
        ];
    }
}
