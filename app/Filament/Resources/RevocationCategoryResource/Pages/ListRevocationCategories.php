<?php

namespace App\Filament\Resources\RevocationCategoryResource\Pages;

use App\Filament\Resources\RevocationCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRevocationCategories extends ListRecords
{
    protected static string $resource = RevocationCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
