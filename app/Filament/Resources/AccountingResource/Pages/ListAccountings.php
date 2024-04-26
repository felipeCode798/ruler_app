<?php

namespace App\Filament\Resources\AccountingResource\Pages;

use App\Filament\Resources\AccountingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListAccountings extends ListRecords
{
    protected static string $resource = AccountingResource::class;
    protected static ?string $title = 'Contabilidad';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Contabilizar'),
        ];
    }
}
