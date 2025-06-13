<?php
// app/Filament/Karyawan/Resources/ArchiveResource.php

namespace App\Filament\Karyawan\Resources;

use App\Filament\Karyawan\Resources\ArchiveResource\Pages;
use App\Models\Archive;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ArchiveResource extends Resource
{
    protected static ?string $model = Archive::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Arsip Dokumen';

    protected static ?string $modelLabel = 'Arsip';

    protected static ?string $pluralModelLabel = 'Arsip';

    protected static ?int $navigationSort = 1;

    // Karyawan hanya bisa view, tidak bisa create/edit/delete
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form hanya untuk view, tidak untuk edit
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Dokumen')
                            ->disabled(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->disabled(),

                        Forms\Components\TextInput::make('category')
                            ->label('Kategori')
                            ->disabled(),

                        Forms\Components\TextInput::make('document_number')
                            ->label('Nomor Dokumen')
                            ->disabled(),

                        Forms\Components\DatePicker::make('document_date')
                            ->label('Tanggal Dokumen')
                            ->disabled(),

                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags')
                            ->disabled(),

                        Forms\Components\Toggle::make('is_public')
                            ->label('Status Publik')
                            ->disabled(),

                        Forms\Components\TextInput::make('file_name')
                            ->label('Nama File')
                            ->disabled(),

                        Forms\Components\TextInput::make('file_type')
                            ->label('Tipe File')
                            ->disabled(),

                        // Perbaikan: Hapus getStateUsing dan gunakan placeholder atau default value
                        Forms\Components\Placeholder::make('file_size_display')
                            ->label('Ukuran File')
                            ->content(fn (Archive $record): string => $record->file_size_human ?? 'N/A'),

                        Forms\Components\Placeholder::make('uploader_name')
                            ->label('Diupload oleh')
                            ->content(fn (?Archive $record): string => $record?->uploader?->name ?? 'N/A'),


                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                // Karyawan hanya bisa lihat arsip publik
                Archive::query()->where('is_public', true)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (Archive $record): string {
                        return $record->title;
                    }),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('file_name')
                    ->label('Nama File')
                    ->limit(30)
                    ->tooltip(function (Archive $record): string {
                        return $record->file_name;
                    }),

                Tables\Columns\TextColumn::make('file_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'doc', 'docx' => 'info',
                        'xls', 'xlsx' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('file_size_human')
                    ->label('Ukuran')
                    ->getStateUsing(fn (Archive $record): string => $record->file_size_human),

                Tables\Columns\TextColumn::make('document_date')
                    ->label('Tanggal Dokumen')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Diupload oleh')
                    ->searchable(),



                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(Archive::getCategories()),

                Tables\Filters\Filter::make('document_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('document_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('document_date', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('file_type')
                    ->label('Tipe File')
                    ->options([
                        'pdf' => 'PDF',
                        'doc' => 'DOC',
                        'docx' => 'DOCX',
                        'xls' => 'XLS',
                        'xlsx' => 'XLSX',
                    ]),
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

                Tables\Actions\ViewAction::make()
                    ->label('Detail'),
            ])
            ->bulkActions([
                // Tidak ada bulk actions untuk karyawan
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Tidak ada arsip')
            ->emptyStateDescription('Belum ada arsip publik yang tersedia.')
            ->emptyStateIcon('heroicon-o-archive-box');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArchives::route('/'),
            'view' => Pages\ViewArchive::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_public', true)
            ->with(['uploader']);
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->title;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Kategori' => $record->category,
            'Tanggal' => $record->document_date->format('d/m/Y'),
        ];
    }
}
