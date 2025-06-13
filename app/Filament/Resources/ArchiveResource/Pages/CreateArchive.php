<?php

// app/Filament/Resources/ArchiveResource/Pages/CreateArchive.php

namespace App\Filament\Resources\ArchiveResource\Pages;

use App\Filament\Resources\ArchiveResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArchive extends CreateRecord
{
    protected static string $resource = ArchiveResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = auth()->id();

        return $data;
    }
}
