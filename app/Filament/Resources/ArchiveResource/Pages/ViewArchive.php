<?php

// app/Filament/Resources/ArchiveResource/Pages/ViewArchive.php

namespace App\Filament\Resources\ArchiveResource\Pages;

use App\Filament\Resources\ArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

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

                    return response()->download(
                        Storage::disk('public')->path($this->record->file_path),
                        $this->record->file_name
                    );
                })
                ->color('success'),
            Actions\EditAction::make(),
        ];
    }
}
