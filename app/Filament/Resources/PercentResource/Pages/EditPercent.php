<?php

namespace App\Filament\Resources\PercentResource\Pages;

use App\Filament\Resources\PercentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPercent extends EditRecord
{
    protected static string $resource = PercentResource::class;
    protected static ?string $title = 'Editar porcentajes';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
