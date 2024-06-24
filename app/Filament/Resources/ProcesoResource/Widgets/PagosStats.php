<?php

namespace App\Filament\Resources\ProcesoResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Pagos;
use App\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PagosStats extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = Auth::id();
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $totalEntradas = Pagos::where('responsible_id', $userId)
            ->where('concepto', 'entrada')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('valor');

        $totalSalidasYGastos = Pagos::where('responsible_id', $userId)
            ->where('concepto', 'salida')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('valor') +
            Expense::where('responsible_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        $totalEntradasPagadas = Pagos::where('responsible_id', $userId)
            ->where('concepto', 'entrada')
            ->where('pagado', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('valor');

        $totalFinal = $totalEntradas - ($totalSalidasYGastos + $totalEntradasPagadas);

        return [
            Stat::make('Total Entradas', number_format($totalEntradas, 2))
                ->description('Total de todas las entradas'),
            Stat::make('Total Salidas y Gastos', number_format($totalSalidasYGastos, 2))
                ->description('Total de todas las salidas y gastos'),
            Stat::make('Total Entradas Pagadas', number_format($totalEntradasPagadas, 2))
                ->description('Total de procesos pagados'),
            Stat::make('Total Final', number_format($totalFinal, 2))
                ->description('Total de cuadre'),
        ];
    }
}
