<?php

// app/Filament/Resources/ArchiveResource/Pages/EditArchive.php

namespace App\Filament\Resources\ArchiveResource\Pages;

use App\Filament\Resources\ArchiveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditArchive extends EditRecord
{
    protected static string $resource = ArchiveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function () {
                    // Delete file from storage
                    if (Storage::disk('public')->exists($this->record->file_path)) {
                        Storage::disk('public')->delete($this->record->file_path);
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If file is updated, handle file replacement
        if (isset($data['file_path']) && $data['file_path'] !== $this->record->file_path) {
            // Delete old file
            if (Storage::disk('public')->exists($this->record->file_path)) {
                Storage::disk('public')->delete($this->record->file_path);
            }
        }

        return $data;
    }
}
