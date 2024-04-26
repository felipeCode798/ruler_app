<?php

namespace App\Filament\Resources\ProcessReturnResource\Pages;

use App\Filament\Resources\ProcessReturnResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProcessReturn extends CreateRecord
{
    protected static string $resource = ProcessReturnResource::class;
    protected static ?string $title = 'Devolución';
}
