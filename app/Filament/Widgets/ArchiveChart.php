<?php

// app/Filament/Widgets/ArchiveChart.php

namespace App\Filament\Widgets;

use App\Models\Archive;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ArchiveChart extends ChartWidget
{
    protected static ?string $heading = 'Upload Arsip per Bulan';

    protected function getData(): array
    {
        // Database-agnostic approach using Eloquent
        $archives = Archive::whereYear('created_at', now()->year)->get();

        // Group by month using Carbon
        $groupedData = $archives->groupBy(function($archive) {
            return $archive->created_at->month;
        });

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $chartData = [];
        $labels = [];

        for ($i = 1; $i <= 12; $i++) {
            $labels[] = $months[$i];
            $chartData[] = $groupedData->has($i) ? $groupedData[$i]->count() : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Upload Arsip',
                    'data' => $chartData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
