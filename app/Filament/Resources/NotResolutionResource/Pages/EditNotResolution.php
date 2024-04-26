<?php

namespace App\Filament\Resources\NotResolutionResource\Pages;

use App\Filament\Resources\NotResolutionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotResolution extends EditRecord
{
    protected static string $resource = NotResolutionResource::class;
    protected static ?string $title = 'Sin resolución';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
