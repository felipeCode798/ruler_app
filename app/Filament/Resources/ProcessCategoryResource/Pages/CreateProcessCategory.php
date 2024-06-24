<?php

namespace App\Filament\Resources\ProcessCategoryResource\Pages;

use App\Filament\Resources\ProcessCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProcessCategory extends CreateRecord
{
    protected static string $resource = ProcessCategoryResource::class;
    protected static ?string $title = 'Crear Proceso';
}
