<?php

namespace App\Filament\Resources\ControversyCategoryResource\Pages;

use App\Filament\Resources\ControversyCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListControversyCategories extends ListRecords
{
    protected static string $resource = ControversyCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
