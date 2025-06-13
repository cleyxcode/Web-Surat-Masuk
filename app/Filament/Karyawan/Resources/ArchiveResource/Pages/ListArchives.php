<?php

// app/Filament/Karyawan/Resources/ArchiveResource/Pages/ListArchives.php

namespace App\Filament\Karyawan\Resources\ArchiveResource\Pages;

use App\Filament\Karyawan\Resources\ArchiveResource;
use Filament\Resources\Pages\ListRecords;

class ListArchives extends ListRecords
{
    protected static string $resource = ArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada action create untuk karyawan
        ];
    }
}
