<?php

namespace App\Filament\Resources\ProcesoResource\Pages;

use App\Filament\Resources\ProcesoResource;
use App\Filament\Resources\ProcesoResource\Widgets\PagosStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProcesos extends ListRecords
{
    protected static string $resource = ProcesoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PagosStats::class
        ];
    }
}
