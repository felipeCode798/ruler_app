<?php

namespace App\Filament\Personal\Resources\SubpoenaResource\Pages;

use App\Filament\Personal\Resources\SubpoenaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubpoena extends EditRecord
{
    protected static string $resource = SubpoenaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
