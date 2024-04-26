<?php

namespace App\Filament\Personal\Resources\RenewallResource\Pages;

use App\Filament\Personal\Resources\RenewallResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRenewalls extends ListRecords
{
    protected static string $resource = RenewallResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
