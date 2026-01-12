<?php

namespace App\Filament\Resources;

use App\Enums\LetterRequestStatus;
use App\Filament\Resources\LetterRequestResource\Pages;
use App\Models\LetterCounter;
use App\Models\LetterRequest;
use App\Models\LetterType;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section as InfolistSection;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LetterRequestResource extends Resource
{
    protected static ?string $model = LetterRequest::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-inbox-stack';
    }

    public static function getNavigationLabel(): string
    {
        return 'Pengajuan Surat';
    }

    public static function getModelLabel(): string
    {
        return 'Pengajuan Surat';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pengajuan Surat';
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    /**
     * Scope the query based on user role.
     * Users only see their own requests, admins see all.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->isUser()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengajuan')
                    ->schema([
                        Select::make('letter_type_id')
                            ->label('Jenis Surat')
                            ->options(LetterType::pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('payload_data', null))
                            ->disabled(fn($context) => $context === 'edit'),
                    ])
                    ->columnSpanFull(),

                Section::make('Data Pengajuan')
                    ->schema(function (callable $get): array {
                        $letterTypeId = $get('letter_type_id');

                        if (!$letterTypeId) {
                            return [
                                Placeholder::make('placeholder')
                                    ->label('')
                                    ->content('Pilih jenis surat terlebih dahulu untuk melihat form pengisian.'),
                            ];
                        }

                        $letterType = LetterType::find($letterTypeId);
                        $formSchema = $letterType?->form_schema ?? [];

                        if (empty($formSchema)) {
                            return [
                                Placeholder::make('no_schema')
                                    ->label('')
                                    ->content('Jenis surat ini tidak memiliki form pengisian.'),
                            ];
                        }

                        $fields = [];
                        foreach ($formSchema as $field) {
                            // Use unique field names prefixed with dynamic_ to avoid conflicts
                            $fieldName = 'dynamic_' . ($field['name'] ?? 'field');
                            $fieldLabel = $field['label'] ?? $field['name'] ?? 'Field';
                            $fieldType = $field['type'] ?? 'text';
                            $isRequired = $field['required'] ?? false;

                            $component = match ($fieldType) {
                                'textarea' => Textarea::make($fieldName)
                                    ->label($fieldLabel)
                                    ->rows(3),
                                'date' => DatePicker::make($fieldName)
                                    ->label($fieldLabel),
                                'number' => TextInput::make($fieldName)
                                    ->label($fieldLabel)
                                    ->numeric(),
                                default => TextInput::make($fieldName)
                                    ->label($fieldLabel),
                            };

                            if ($isRequired) {
                                $component = $component->required();
                            }

                            $fields[] = $component;
                        }

                        return $fields;
                    })
                    ->visible(fn(callable $get): bool => $get('letter_type_id') !== null)
                    ->columnSpanFull(),

                // Admin-only fields (hidden during creation)
                Section::make('Status & Dokumen')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(LetterRequestStatus::class)
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('letter_number')
                            ->label('Nomor Surat')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('notes')
                            ->label('Catatan Admin')
                            ->rows(2)
                            ->visible(fn() => auth()->user()?->isAdmin()),
                    ])
                    ->visible(fn($context) => $context === 'edit')
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->searchable()
                    ->visible(fn() => auth()->user()?->isAdmin()),
                TextColumn::make('letterType.name')
                    ->label('Jenis Surat')
                    ->badge()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('letter_number')
                    ->label('Nomor Surat')
                    ->placeholder('-')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(LetterRequestStatus::class),
                SelectFilter::make('letter_type_id')
                    ->label('Jenis Surat')
                    ->options(LetterType::pluck('name', 'id')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(
                        fn(LetterRequest $record) =>
                        auth()->user()?->isAdmin() && $record->status === LetterRequestStatus::Pending
                    ),

                // Generate Number Action - Admin only
                Action::make('generate_number')
                    ->label('Generate')
                    ->color('info')
                    ->outlined()
                    ->requiresConfirmation()
                    ->modalHeading('Generate Nomor Surat')
                    ->modalDescription('Apakah Anda yakin ingin generate nomor surat untuk pengajuan ini?')
                    ->visible(
                        fn(LetterRequest $record) =>
                        auth()->user()?->isAdmin() &&
                        $record->status === LetterRequestStatus::Pending &&
                        empty($record->letter_number)
                    )
                    ->action(function (LetterRequest $record) {
                        try {
                            $letterNumber = DB::transaction(function () use ($record) {
                                // Get or create counter for current year with row lock
                                $year = date('Y');
                                $counter = LetterCounter::firstOrCreate(['year' => $year], ['last_number' => 0]);

                                // Lock the row to prevent race conditions
                                $counter = LetterCounter::where('id', $counter->id)->lockForUpdate()->first();

                                // Increment the counter
                                $newNumber = $counter->last_number + 1;
                                $counter->update(['last_number' => $newNumber]);

                                // Format: [No]/[Month]/BJR/[Year]/[Code]
                                $code = $record->letterType->code;
                                return sprintf("%03d/%s/BJR/%s/%s", $newNumber, date('m'), $year, $code);
                            });

                            $record->update([
                                'letter_number' => $letterNumber,
                                'status' => LetterRequestStatus::Processing,
                            ]);

                            Notification::make()
                                ->title('Nomor surat berhasil digenerate')
                                ->body("Nomor surat: {$letterNumber}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal generate nomor surat')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Upload Final Action - Admin only
                Action::make('upload_final')
                    ->label('Upload Dokumen')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->outlined()
                    ->visible(
                        fn(LetterRequest $record) =>
                        auth()->user()?->isAdmin() &&
                        $record->status === LetterRequestStatus::Processing &&
                        !empty($record->letter_number)
                    )
                    ->form([
                        FileUpload::make('file')
                            ->label('File Surat (PDF)')
                            ->disk('public')
                            ->directory('letters')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120) // 5MB
                            ->required(),
                    ])
                    ->action(function (LetterRequest $record, array $data) {
                        $record->update([
                            'file_path' => $data['file'],
                            'status' => LetterRequestStatus::Completed,
                        ]);

                        Notification::make()
                            ->title('Dokumen berhasil diupload')
                            ->body('Status pengajuan telah diubah menjadi Completed.')
                            ->success()
                            ->send();
                    }),

                // Reject Action - Admin only
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->outlined()
                    ->requiresConfirmation()
                    ->visible(
                        fn(LetterRequest $record) =>
                        auth()->user()?->isAdmin() &&
                        in_array($record->status, [LetterRequestStatus::Pending, LetterRequestStatus::Processing])
                    )
                    ->form([
                        Textarea::make('rejection_notes')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (LetterRequest $record, array $data) {
                        $record->update([
                            'status' => LetterRequestStatus::Rejected,
                            'notes' => $data['rejection_notes'],
                        ]);

                        Notification::make()
                            ->title('Pengajuan ditolak')
                            ->success()
                            ->send();
                    }),

                // Download Action - Admin & User if completed
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->outlined()
                    ->visible(
                        fn(LetterRequest $record) =>
                        $record->status === LetterRequestStatus::Completed &&
                        !empty($record->file_path)
                    )
                    ->url(fn(LetterRequest $record) => route('download.letter', $record)),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                InfolistSection::make('Informasi Pengajuan')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Pemohon'),
                        TextEntry::make('letterType.name')
                            ->label('Jenis Surat')
                            ->badge(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('letter_number')
                            ->label('Nomor Surat')
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Tanggal Pengajuan')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                InfolistSection::make('Data Pengajuan')
                    ->schema(function (LetterRequest $record): array {
                        $letterType = $record->letterType;
                        $formSchema = $letterType?->form_schema ?? [];
                        $payloadData = $record->payload_data ?? [];

                        if (empty($formSchema)) {
                            return [
                                TextEntry::make('no_data')
                                    ->label('')
                                    ->state('Tidak ada data form.'),
                            ];
                        }

                        $entries = [];
                        foreach ($formSchema as $field) {
                            $fieldName = $field['name'] ?? 'field';
                            $fieldLabel = $field['label'] ?? $fieldName;
                            $value = $payloadData[$fieldName] ?? '-';

                            $entries[] = TextEntry::make("payload_data.{$fieldName}")
                                ->label($fieldLabel)
                                ->state($value)
                                ->copyable();
                        }

                        return $entries;
                    })
                    ->columns(2)
                    ->columnSpanFull(),

                InfolistSection::make('Catatan Admin')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('')
                            ->placeholder('Tidak ada catatan.'),
                    ])
                    ->visible(fn(LetterRequest $record) => !empty($record->notes))
                    ->columnSpanFull(),
            ]);
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
            'index' => Pages\ListLetterRequests::route('/'),
            'create' => Pages\CreateLetterRequest::route('/create'),
            'view' => Pages\ViewLetterRequest::route('/{record}'),
            'edit' => Pages\EditLetterRequest::route('/{record}/edit'),
        ];
    }
}
