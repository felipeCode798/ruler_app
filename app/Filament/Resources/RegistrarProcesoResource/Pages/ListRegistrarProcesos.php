<?php

namespace App\Filament\Resources\RegistrarProcesoResource\Pages;

use App\Filament\Resources\RegistrarProcesoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRegistrarProcesos extends ListRecords
{
    protected static string $resource = RegistrarProcesoResource::class;
    protected static ?string $title = 'Resgistrar Proceso';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Registrar Proceso'),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('Todos'),
            'Cobro Coactivo' => Tab::make()->query(fn ($query) =>  $query->where('processcategory_id', 1)),
            'Adeudo' => Tab::make()->query(fn ($query) =>  $query->where('processcategory_id', 2)),
            'Sin Resolucion' => Tab::make()->query(fn ($query) =>  $query->where('processcategory_id', 3)),
            'Acuedo de Pago' => Tab::make()->query(fn ($query) =>  $query->where('processcategory_id', 4)),
            'Prescripcion' => Tab::make()->query(fn ($query) =>  $query->where('processcategory_id', 5)),
            'Comparendo' => Tab::make()->query(fn ($query) =>  $query->where('processcategory_id', 6)),
            'Controversia' => Tab::make()->query(fn ($query) =>  $query->where('processcategory_id', 7)),
            'Cursos' => Tab::make()->query(fn ($query) =>  $query->where('processcategory_id', 8)),
            'Licencia' => Tab::make()->query(fn ($query) =>  $query->where('processcategory_id', 9)),
            'Renovacion' => Tab::make()->query(fn ($query) =>  $query->where('processcategory_id', 10)),
        ];
    }
}
