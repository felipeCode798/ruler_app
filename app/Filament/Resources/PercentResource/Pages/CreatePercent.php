<?php

namespace App\Filament\Resources\PercentResource\Pages;

use App\Filament\Resources\PercentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePercent extends CreateRecord
{
    protected static string $resource = PercentResource::class;
    protected static ?string $title = 'Agregar porcentajes';
}
