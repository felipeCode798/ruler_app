<?php

namespace App\Filament\Resources\LicensesSetupCategoryResource\Pages;

use App\Filament\Resources\LicensesSetupCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLicensesSetupCategories extends ListRecords
{
    protected static string $resource = LicensesSetupCategoryResource::class;
    protected static ?string $title = 'Configuracion De Licencias';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Registrar Configuracion'),
        ];
    }
}
