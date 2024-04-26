<?php

namespace App\Filament\Personal\Resources\LicensesResource\Pages;

use App\Filament\Personal\Resources\LicensesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLicenses extends EditRecord
{
    protected static string $resource = LicensesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
