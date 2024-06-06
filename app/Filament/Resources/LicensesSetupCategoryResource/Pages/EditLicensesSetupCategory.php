<?php

namespace App\Filament\Resources\LicensesSetupCategoryResource\Pages;

use App\Filament\Resources\LicensesSetupCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLicensesSetupCategory extends EditRecord
{
    protected static string $resource = LicensesSetupCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
