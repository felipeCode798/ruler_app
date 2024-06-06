<?php

namespace App\Filament\Resources\RevocationResource\Pages;

use App\Filament\Resources\RevocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListRevocations extends ListRecords
{
    protected static string $resource = RevocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array{
        return [
            null => Tab::make('Todos'),
            'Pendiente' => Tab::make()->query(fn ($query) => $query->where('status', 'Pendiente')),
            'En Proceso' => Tab::make()->query(fn ($query) => $query->where('status', 'En Proceso')),
            'Finalizado' => Tab::make()->query(fn ($query) => $query->where('status', 'Finalizado')),
        ];
    }
}
