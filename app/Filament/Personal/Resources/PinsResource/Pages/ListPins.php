<?php

namespace App\Filament\Personal\Resources\PinsResource\Pages;

use App\Filament\Personal\Resources\PinsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPins extends ListRecords
{
    protected static string $resource = PinsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
