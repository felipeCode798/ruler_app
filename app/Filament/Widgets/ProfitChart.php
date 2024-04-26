<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
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

class ProfitChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    public ?string $filter = 'year';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        // Obtener las etiquetas según el filtro seleccionado
        $labels = $this->getLabels();

        return [
            'datasets' => [
                [
                    'label' => 'Ganancias por mes',
                    'data' => $this->getDataProfit(),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getLabels(): array
    {
        $activeFilter = $this->filter;

        switch ($activeFilter) {
            case 'today':
                // Hoy
                $labels = [now()->translatedFormat('d/m/Y')];
                break;
            case 'week':
                // Esta semana
                $labels = [
                    'Lunes',
                    'Martes',
                    'Miércoles',
                    'Jueves',
                    'Viernes',
                    'Sábado',
                    'Domingo',
                ];
                break;
            case 'year':
                // Este año
                $labels = [
                    'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                    'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic',
                ];
                break;
            default:
                $labels = [];
                break;
        }

        return $labels;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hoy',
            'week' => 'Semana',
            'year' => 'Este Año',
        ];
    }

    protected function getDataProfit() {

        $activeFilter = $this->filter;
        $dataProfit = [];

        switch ($activeFilter) {
            case 'today':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();

                break;
            case 'week':
                $startDate = now()->startOfWeek()->subWeek();
                $endDate = now()->endOfWeek()->subWeek();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            default:
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
        }

        for ($i = 1; $i <= 12; $i++) {
            $totalProfit = DebitPayments::whereBetween('created_at', [$startDate, $endDate])
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', now()->year)
                ->sum('value') +
                PinsPayments::whereBetween('created_at', [$startDate, $endDate])
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', now()->year)
                    ->sum('value') +
                Payments::whereBetween('created_at', [$startDate, $endDate])
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', now()->year)
                    ->sum('value') +
                SubpoenaPayments::whereBetween('created_at', [$startDate, $endDate])
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', now()->year)
                    ->sum('value') +
                PaymentAgreementPayments::whereBetween('created_at', [$startDate, $endDate])
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', now()->year)
                    ->sum('value') +
                CoerciveCollectionPayments::whereBetween('created_at', [$startDate, $endDate])
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', now()->year)
                    ->sum('value') +
                NotResolutionPayments::whereBetween('created_at', [$startDate, $endDate])
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', now()->year)
                    ->sum('value') +
                PrescriptionPayments::whereBetween('created_at', [$startDate, $endDate])
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', now()->year)
                    ->sum('value') +
                CoursePayments::whereBetween('created_at', [$startDate, $endDate])
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', now()->year)
                    ->sum('value') +
                RenewallPayments::whereBetween('created_at', [$startDate, $endDate])
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', now()->year)
                    ->sum('value') +
                ControversyPayments::whereBetween('created_at', [$startDate, $endDate])
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', now()->year)
                    ->sum('value');

            $dataProfit[] = $totalProfit;
        }

        return $dataProfit;
    }
}
