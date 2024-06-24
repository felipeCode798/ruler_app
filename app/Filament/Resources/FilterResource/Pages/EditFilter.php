<?php

namespace App\Filament\Resources\FilterResource\Pages;

use App\Filament\Resources\FilterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFilter extends EditRecord
{
    protected static string $resource = FilterResource::class;
    protected static ?string $title = 'Editar Filtro';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
