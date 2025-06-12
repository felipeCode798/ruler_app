<?php

namespace App\Filament\Resources\RevocationCategoryResource\Pages;

use App\Filament\Resources\RevocationCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRevocationCategory extends EditRecord
{
    protected static string $resource = RevocationCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
