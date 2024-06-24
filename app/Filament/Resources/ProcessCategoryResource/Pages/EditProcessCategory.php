<?php

namespace App\Filament\Resources\ProcessCategoryResource\Pages;

use App\Filament\Resources\ProcessCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcessCategory extends EditRecord
{
    protected static string $resource = ProcessCategoryResource::class;
    protected static ?string $title = 'Editar Proceso';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
