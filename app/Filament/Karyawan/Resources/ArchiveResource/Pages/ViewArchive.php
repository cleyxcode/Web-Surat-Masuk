<?php

// app/Filament/Karyawan/Resources/ArchiveResource/Pages/ViewArchive.php

namespace App\Filament\Karyawan\Resources\ArchiveResource\Pages;

use App\Filament\Karyawan\Resources\ArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ViewArchive extends ViewRecord
{
    protected static string $resource = ArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download File')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $this->record->incrementDownloadCount();

                    Notification::make()
                        ->title('File berhasil didownload')
                        ->success()
                        ->send();

                    return response()->download(
                        Storage::disk('public')->path($this->record->file_path),
                        $this->record->file_name
                    );
                })
                ->color('success'),
        ];
    }
}
