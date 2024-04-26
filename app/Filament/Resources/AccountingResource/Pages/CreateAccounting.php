<?php

namespace App\Filament\Resources\AccountingResource\Pages;

use App\Filament\Resources\AccountingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAccounting extends CreateRecord
{
    protected static string $resource = AccountingResource::class;
    protected static ?string $title = 'Crear Contabilidad';
}
