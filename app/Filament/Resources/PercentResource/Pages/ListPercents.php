<?php

namespace App\Filament\Resources\PercentResource\Pages;

use App\Filament\Resources\PercentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Models\Percent;

class ListPercents extends ListRecords
{
    protected static string $resource = PercentResource::class;
    protected static ?string $title = 'Porcentajes de procesos';

    protected function getHeaderActions(): array
    {
        $existsPercent = Percent::exists();

        if (!$existsPercent) {
            return [
                Actions\CreateAction::make()->label('Agregar porcentajes'),
            ];
        }

        return [];
    }
}
