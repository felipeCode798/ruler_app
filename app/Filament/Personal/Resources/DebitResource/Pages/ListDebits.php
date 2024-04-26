<?php

namespace App\Filament\Personal\Resources\DebitResource\Pages;

use App\Filament\Personal\Resources\DebitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDebits extends ListRecords
{
    protected static string $resource = DebitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
