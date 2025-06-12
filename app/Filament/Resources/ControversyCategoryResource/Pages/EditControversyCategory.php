<?php

namespace App\Filament\Resources\ControversyCategoryResource\Pages;

use App\Filament\Resources\ControversyCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditControversyCategory extends EditRecord
{
    protected static string $resource = ControversyCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
