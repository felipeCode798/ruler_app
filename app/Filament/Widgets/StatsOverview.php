<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\User;
use App\Models\DebitPayments;
use App\Models\PinsPayments;
use App\Models\Payments;
use App\Models\SubpoenaPayments;
use App\Models\PaymentAgreementPayments;
use App\Models\CoerciveCollectionPayments;
use App\Models\NotResolutionPayments;
use App\Models\PrescriptionPayments;
use App\Models\CoursePayments;
use App\Models\RenewallPayments;
use App\Models\ControversyPayments;
use App\Models\Licenses;
use App\Models\Pins;
use App\Models\Debit;
use App\Models\Subpoena;
use App\Models\PaymentAgreement;
use App\Models\CoerciveCollection;
use App\Models\NotResolutions;
use App\Models\Prescription;
use App\Models\Course;
use App\Models\Renewall;
use App\Models\Controversy;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total de clientes
        $totalClientes = User::whereHas('roles', function ($query) {
            $query->where('name', 'cliente');
        })->count();

        // Total de clientes del mes anterior
        $lastMonthTotalClientes = User::whereHas('roles', function ($query) {
            $query->where('name', 'cliente');
        })->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();

        // Diferencia en el total de clientes
        $diffTotalClientes = $totalClientes - $lastMonthTotalClientes;

        // Total de controversias
        $totalControversies = Controversy::count();

        // Total de controversias del mes anterior
        $lastMonthTotalControversies = Controversy::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();

        // Diferencia en el total de controversias
        $diffTotalControversies = $totalControversies - $lastMonthTotalControversies;

        // Total de devoluciones
        $totalSumDeb = Licenses::where('state', 'return')->sum('total_value') +
            Pins::where('state', 'return')->sum('total_value') +
            Debit::where('state', 'return')->sum('total_value') +
            Subpoena::where('state', 'return')->sum('total_value') +
            PaymentAgreement::where('state', 'return')->sum('total_value') +
            CoerciveCollection::where('state', 'return')->sum('total_value') +
            NotResolutions::where('state', 'return')->sum('total_value') +
            Prescription::where('state', 'return')->sum('total_value') +
            Course::where('state', 'return')->sum('total_value') +
            Renewall::where('state', 'return')->sum('total_value') +
            Controversy::where('state', 'return')->sum('total_value');

        // Total de devoluciones del mes anterior
        $lastMonthTotalSumDeb = Licenses::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('total_value') +
            Pins::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Debit::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Subpoena::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            PaymentAgreement::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            CoerciveCollection::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            NotResolutions::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Prescription::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Course::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Renewall::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Controversy::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value');

        // Diferencia en el total de devoluciones
        $diffTotalSumDeb = $totalSumDeb - $lastMonthTotalSumDeb;

        // Total de ganancias
        $totalSum = DebitPayments::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('value') +
            PinsPayments::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('value') +
            Payments::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('value') +
            SubpoenaPayments::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('value') +
            PaymentAgreementPayments::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('value') +
            CoerciveCollectionPayments::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('value') +
            NotResolutionPayments::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('value') +
            PrescriptionPayments::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('value') +
            CoursePayments::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('value') +
            RenewallPayments::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('value') +
            ControversyPayments::whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('value');

        // Total de ganancias del mes anterior
        $lastMonthTotalSum = Licenses::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('total_value') +
            Pins::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Debit::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Subpoena::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            PaymentAgreement::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            CoerciveCollection::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            NotResolutions::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Prescription::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Course::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Renewall::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value') +
            Controversy::whereYear('created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                ->sum('total_value');

        // Diferencia en el total de ganancias
        $diffTotalSum = $totalSum - $lastMonthTotalSum;

        // Porcentaje de cambio en el total de ganancias
        $percentChange = $lastMonthTotalSum != 0 ? (($totalSum - $lastMonthTotalSum) / $lastMonthTotalSum) * 100 : 0;

        return [
            Stat::make('Total clientes', $totalClientes)
                ->description("Incremento: $diffTotalClientes")
                ->descriptionIcon('heroicon-m-arrow-trending-up'),

            Stat::make('Total Controversias', $totalControversies)
                ->description("Incremento: $diffTotalControversies")
                ->descriptionIcon('heroicon-m-arrow-trending-up'),

            Stat::make('Total Devoluciones', $totalSumDeb)
                ->description("Incremento: $diffTotalSumDeb")
                ->descriptionIcon('heroicon-m-arrow-trending-up'),

            Stat::make('Total Ganacias', $totalSum)
                ->description("Incremento: " . number_format($percentChange, 2) . "%")
                ->descriptionIcon('heroicon-m-arrow-trending-up'),

            // Stat::make('Unique views', '192.1k')
            //     ->description('32k increase')
            //     ->descriptionIcon('heroicon-m-arrow-trending-up')
            //     ->chart([7, 2, 10, 3, 15, 4, 17])
            //     ->color('success'),
        ];
    }
}
