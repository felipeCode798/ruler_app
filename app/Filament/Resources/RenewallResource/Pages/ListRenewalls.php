<?php

namespace App\Filament\Resources\RenewallResource\Pages;

use App\Filament\Resources\RenewallResource;
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
