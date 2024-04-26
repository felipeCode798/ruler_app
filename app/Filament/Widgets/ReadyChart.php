<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
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

class ReadyChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $labels = ['Licenses', 'Pins', 'Debit', 'Subpoena', 'Payment Agreement', 'Coercive Collection', 'Not Resolutions', 'Prescription', 'Course', 'Renewall', 'Controversy'];
        $data = [];

        // Obtener el porcentaje de registros en estado "ready" para cada modelo
        foreach ($labels as $label) {
            $data[] = $this->getReadyPercentage($label);
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#4CAF50', '#FFEB3B', '#9C27B0', '#00BCD4', '#F44336'],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getReadyPercentage(string $modelName): float
    {
        $modelClass = 'App\Models\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $modelName)));
        $readyCount = $modelClass::where('state', 'ready')->count();
        $totalCount = $modelClass::count();

        if ($totalCount == 0) {
            return 0;
        }

        return ($readyCount / $totalCount) * 100;
    }

}
