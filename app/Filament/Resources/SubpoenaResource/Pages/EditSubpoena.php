<?php

namespace App\Filament\Resources\SubpoenaResource\Pages;

use App\Filament\Resources\SubpoenaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubpoena extends EditRecord
{
    protected static string $resource = SubpoenaResource::class;
    protected static ?string $title = 'Editar Comparendo';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
