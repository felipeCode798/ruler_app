<?php

namespace App\Filament\Personal\Resources\NotResolutionsResource\Pages;

use App\Filament\Personal\Resources\NotResolutionsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotResolutions extends EditRecord
{
    protected static string $resource = NotResolutionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
