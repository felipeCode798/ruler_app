<?php

namespace App\Filament\Resources\ProcessReturnResource\Pages;

use App\Filament\Resources\ProcessReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcessReturn extends EditRecord
{
    protected static string $resource = ProcessReturnResource::class;
    protected static ?string $title = 'Devolución';

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }
}
