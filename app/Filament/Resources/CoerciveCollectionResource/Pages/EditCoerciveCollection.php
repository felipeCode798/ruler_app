<?php

namespace App\Filament\Resources\CoerciveCollectionResource\Pages;

use App\Filament\Resources\CoerciveCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoerciveCollection extends EditRecord
{
    protected static string $resource = CoerciveCollectionResource::class;
    protected static ?string $title = 'Editar Cobro Coactivo';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
