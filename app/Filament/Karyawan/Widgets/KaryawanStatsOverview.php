<?php

// app/Filament/Karyawan/Widgets/KaryawanStatsOverview.php

namespace App\Filament\Karyawan\Widgets;

use App\Models\Archive;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KaryawanStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Arsip Publik', Archive::where('is_public', true)->count())
                ->description('Arsip yang dapat diakses')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),

            Stat::make('Arsip Terbaru', Archive::where('is_public', true)->whereMonth('created_at', now()->month)->count())
                ->description('Arsip bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Kategori Tersedia', Archive::where('is_public', true)->distinct('category')->count('category'))
                ->description('Kategori arsip')
                ->descriptionIcon('heroicon-m-folder')
                ->color('warning'),


        ];
    }
}
