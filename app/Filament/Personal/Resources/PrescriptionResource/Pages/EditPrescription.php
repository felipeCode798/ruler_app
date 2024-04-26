<?php

namespace App\Filament\Personal\Resources\PrescriptionResource\Pages;

use App\Filament\Personal\Resources\PrescriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrescription extends EditRecord
{
    protected static string $resource = PrescriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
