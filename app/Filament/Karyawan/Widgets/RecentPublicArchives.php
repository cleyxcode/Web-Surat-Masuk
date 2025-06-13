<?php

// app/Filament/Karyawan/Widgets/RecentPublicArchives.php

namespace App\Filament\Karyawan\Widgets;

use App\Models\Archive;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class RecentPublicArchives extends BaseWidget
{
    protected static ?string $heading = 'Arsip Terbaru';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Archive::query()
                    ->where('is_public', true)
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->limit(50)
                    ->tooltip(function (Archive $record): string {
                        return $record->title;
                    }),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge(),

                Tables\Columns\TextColumn::make('file_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'doc', 'docx' => 'info',
                        'xls', 'xlsx' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Diupload oleh'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Archive $record) {
                        $record->incrementDownloadCount();

                        Notification::make()
                            ->title('File berhasil didownload')
                            ->success()
                            ->send();

                        return response()->download(
                            Storage::disk('public')->path($record->file_path),
                            $record->file_name
                        );
                    })
                    ->color('success'),

                Tables\Actions\Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Archive $record): string => route('filament.karyawan.resources.archives.view', $record)),
            ]);
    }
}
