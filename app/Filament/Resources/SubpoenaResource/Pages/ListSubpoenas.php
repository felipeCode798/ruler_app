<?php

namespace App\Filament\Resources\SubpoenaResource\Pages;

use App\Filament\Resources\SubpoenaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubpoenas extends ListRecords
{
    protected static string $resource = SubpoenaResource::class;
    protected static ?string $title = 'Comparendos';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Comparendo'),
        ];
    }
}
