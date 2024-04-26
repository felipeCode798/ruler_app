<?php

namespace App\Filament\Resources\PinResource\Pages;

use App\Filament\Resources\PinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPins extends ListRecords
{
    protected static string $resource = PinResource::class;
    protected static ?string $title = 'Pines';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
