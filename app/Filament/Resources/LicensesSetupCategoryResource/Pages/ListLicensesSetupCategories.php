<?php

namespace App\Filament\Resources\LicensesSetupCategoryResource\Pages;

use App\Filament\Resources\LicensesSetupCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLicensesSetupCategories extends ListRecords
{
    protected static string $resource = LicensesSetupCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
