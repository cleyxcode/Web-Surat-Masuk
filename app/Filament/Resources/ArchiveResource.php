<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArchiveResource\Pages;
use App\Models\Archive;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
class ArchiveResource extends Resource
{
    protected static ?string $navigationGroup = 'Manajemen Arsip';

    protected static ?string $model = Archive::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Kelola Arsip';

    protected static ?string $modelLabel = 'Arsip';

    protected static ?string $pluralModelLabel = 'Arsip';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Dokumen')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),

                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options(Archive::getCategories())
                            ->required(),

                        Forms\Components\TextInput::make('document_number')
                            ->label('Nomor Dokumen')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('document_date')
                            ->label('Tanggal Dokumen')
                            ->required()
                            ->default(now()),

                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags')
                            ->placeholder('Masukkan tags dan tekan Enter'),

                        Forms\Components\Toggle::make('is_public')
                            ->label('Publik (Dapat diakses semua karyawan)')
                            ->default(false),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File')
                            ->disk('public')
                            ->directory('archives')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                            ->maxSize(10240) // 10MB
                            ->required(fn (string $context): bool => $context === 'create')
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $file = $state;
                                    $set('file_name', $file->getClientOriginalName());
                                    $set('file_type', $file->getClientOriginalExtension());
                                    $set('file_size', $file->getSize());
                                }
                            }),

                        Forms\Components\Hidden::make('file_name'),
                        Forms\Components\Hidden::make('file_type'),
                        Forms\Components\Hidden::make('file_size'),
                        Forms\Components\Hidden::make('uploaded_by')
                            ->default(auth()->id()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

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

                Tables\Columns\TextColumn::make('download_count')
                    ->label('Download')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Publik')
                    ->boolean(),

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

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Status Publik'),
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

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Archive $record) {
                        // Delete file from storage
                        if (Storage::disk('public')->exists($record->file_path)) {
                            Storage::disk('public')->delete($record->file_path);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Delete files from storage
                            foreach ($records as $record) {
                                if (Storage::disk('public')->exists($record->file_path)) {
                                    Storage::disk('public')->delete($record->file_path);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'create' => Pages\CreateArchive::route('/create'),
            'view' => Pages\ViewArchive::route('/{record}'),
            'edit' => Pages\EditArchive::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['uploader']);
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Kategori' => $record->category,
            'Tanggal' => $record->document_date->format('d/m/Y'),
        ];
    }
}
