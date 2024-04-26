<?php

namespace App\Filament\Resources\DebitResource\Pages;

use App\Filament\Resources\DebitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDebit extends EditRecord
{
    protected static string $resource = DebitResource::class;
    protected static ?string $title = 'Editar adeudo';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
