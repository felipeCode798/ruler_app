<?php

namespace App\Filament\Resources\LicensesSetupCategoryResource\Pages;

use App\Filament\Resources\LicensesSetupCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLicensesSetupCategory extends EditRecord
{
    protected static string $resource = LicensesSetupCategoryResource::class;
    protected static ?string $title = 'Editar Categoria de Licencia';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
