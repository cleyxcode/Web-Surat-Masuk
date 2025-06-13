<?php

// app/Filament/Widgets/StatsOverview.php

namespace App\Filament\Widgets;

use App\Models\Archive;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
             Stat::make('Total Download', Archive::sum('download_count'))
                ->description('Total file yang didownload')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('primary'),
            Stat::make('Total Pengguna', User::count())
                ->description('Jumlah seluruh pengguna')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Total Arsip', Archive::count())
                ->description('Jumlah seluruh arsip')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            Stat::make('Arsip Bulan Ini', Archive::whereMonth('created_at', now()->month)->count())
                ->description('Arsip yang diupload bulan ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),


        ];
    }
}
