<?php

namespace App\Filament\Resources\ControversyResource\Pages;

use App\Filament\Resources\ControversyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListControversies extends ListRecords
{
    protected static string $resource = ControversyResource::class;
    protected static ?string $title = 'Controversias';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Crear Controversia'),
        ];
    }
}
