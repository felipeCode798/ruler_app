<?php

namespace App\Filament\Personal\Resources\DebitResource\Pages;

use App\Filament\Personal\Resources\DebitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDebit extends CreateRecord
{
    protected static string $resource = DebitResource::class;
}
