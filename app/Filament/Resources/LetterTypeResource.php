<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterTypeResource\Pages;
use App\Models\LetterType;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LetterTypeResource extends Resource
{
    protected static ?string $model = LetterType::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function getNavigationLabel(): string
    {
        return 'Jenis Surat';
    }

    public static function getModelLabel(): string
    {
        return 'Jenis Surat';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Jenis Surat';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jenis Surat')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Jenis Surat')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(10)
                            ->helperText('Contoh: SK, SP, SPH'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Skema Form Dinamis')
                    ->description('Definisikan field-field yang akan muncul di form pengajuan surat')
                    ->schema([
                        Repeater::make('form_schema')
                            ->label('')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Field')
                                    ->required()
                                    ->helperText('Gunakan snake_case, contoh: nama_karyawan'),
                                TextInput::make('label')
                                    ->label('Label')
                                    ->required(),
                                Select::make('type')
                                    ->label('Tipe Input')
                                    ->options([
                                        'text' => 'Teks',
                                        'textarea' => 'Teks Panjang',
                                        'date' => 'Tanggal',
                                        'number' => 'Angka',
                                    ])
                                    ->required(),
                                Toggle::make('required')
                                    ->label('Wajib Diisi')
                                    ->default(true),
                            ])
                            ->columns(4)
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['label'] ?? null),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nama Jenis Surat')
                    ->searchable(),
                TextColumn::make('form_schema')
                    ->label('Jumlah Field')
                    ->formatStateUsing(fn($state) => is_array($state) ? count($state) . ' field' : '0 field'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListLetterTypes::route('/'),
            'create' => Pages\CreateLetterType::route('/create'),
            'edit' => Pages\EditLetterType::route('/{record}/edit'),
        ];
    }
}
