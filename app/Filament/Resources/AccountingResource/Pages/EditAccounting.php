<?php

namespace App\Filament\Resources\AccountingResource\Pages;

use App\Filament\Resources\AccountingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccounting extends EditRecord
{
    protected static string $resource = AccountingResource::class;
    protected static ?string $title = 'Editar Contabilidad';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
