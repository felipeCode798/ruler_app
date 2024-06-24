<?php

namespace App\Filament\Resources\CategoryRevocationResource\Pages;

use App\Filament\Resources\CategoryRevocationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoryRevocation extends EditRecord
{
    protected static string $resource = CategoryRevocationResource::class;
    protected static ?string $title = 'Editar Categoria';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
