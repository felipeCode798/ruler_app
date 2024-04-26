<?php

namespace App\Filament\Personal\Resources\ControversyResource\Pages;

use App\Filament\Personal\Resources\ControversyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListControversies extends ListRecords
{
    protected static string $resource = ControversyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
