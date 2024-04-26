<?php

namespace App\Filament\Resources\DebitResource\Pages;

use App\Filament\Resources\DebitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDebits extends ListRecords
{
    protected static string $resource = DebitResource::class;
    protected static ?string $title = 'Adeudos';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Adeudo'),
        ];
    }
}
