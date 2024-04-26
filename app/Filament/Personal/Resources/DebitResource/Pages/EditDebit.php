<?php

namespace App\Filament\Personal\Resources\DebitResource\Pages;

use App\Filament\Personal\Resources\DebitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDebit extends EditRecord
{
    protected static string $resource = DebitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
