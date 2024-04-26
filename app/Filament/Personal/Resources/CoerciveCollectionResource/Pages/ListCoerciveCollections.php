<?php

namespace App\Filament\Personal\Resources\CoerciveCollectionResource\Pages;

use App\Filament\Personal\Resources\CoerciveCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoerciveCollections extends ListRecords
{
    protected static string $resource = CoerciveCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
