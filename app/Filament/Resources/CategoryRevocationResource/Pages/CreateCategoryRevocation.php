<?php

namespace App\Filament\Resources\CategoryRevocationResource\Pages;

use App\Filament\Resources\CategoryRevocationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryRevocation extends CreateRecord
{
    protected static string $resource = CategoryRevocationResource::class;
    protected static ?string $title = 'Crear Categoria';
}
