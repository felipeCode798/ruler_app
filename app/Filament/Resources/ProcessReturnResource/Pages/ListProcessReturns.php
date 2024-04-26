<?php

namespace App\Filament\Resources\ProcessReturnResource\Pages;

use App\Filament\Resources\ProcessReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProcessReturns extends ListRecords
{
    protected static string $resource = ProcessReturnResource::class;
    protected static ?string $title = 'Devoluciones';

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
