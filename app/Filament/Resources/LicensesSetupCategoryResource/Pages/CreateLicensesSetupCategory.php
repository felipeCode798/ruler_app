<?php

namespace App\Filament\Resources\LicensesSetupCategoryResource\Pages;

use App\Filament\Resources\LicensesSetupCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLicensesSetupCategory extends CreateRecord
{
    protected static string $resource = LicensesSetupCategoryResource::class;
    protected static ?string $title = 'Crear Configuracion De Licencias';
}
