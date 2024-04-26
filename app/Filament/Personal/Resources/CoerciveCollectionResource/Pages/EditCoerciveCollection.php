<?php

namespace App\Filament\Personal\Resources\CoerciveCollectionResource\Pages;

use App\Filament\Personal\Resources\CoerciveCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoerciveCollection extends EditRecord
{
    protected static string $resource = CoerciveCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
