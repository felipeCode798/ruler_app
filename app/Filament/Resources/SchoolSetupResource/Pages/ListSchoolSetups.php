<?php

namespace App\Filament\Resources\SchoolSetupResource\Pages;

use App\Filament\Resources\SchoolSetupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolSetups extends ListRecords
{
    protected static string $resource = SchoolSetupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
