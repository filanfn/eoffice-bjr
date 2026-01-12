<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterCounterResource\Pages;
use App\Models\LetterCounter;
use App\Models\LetterRequest;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LetterCounterResource extends Resource
{
    protected static ?string $model = LetterCounter::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penomoran')
                    ->schema([
                        TextInput::make('year')
                            ->label('Tahun')
                            ->disabled(),
                        TextInput::make('last_number')
                            ->label('Nomor Terakhir')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')
                    ->label('Tahun')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('last_number')
                    ->label('Total Nomor Terpakai')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('generated_numbers')
                    ->label('Daftar Nomor Surat')
                    ->state(function ($record) {
                        // Get all letter numbers from letter_requests for this year
                        $letterNumbers = LetterRequest::whereNotNull('letter_number')
                            ->whereYear('created_at', $record->year)
                            ->orderBy('id', 'desc')
                            ->limit(5)
                            ->pluck('letter_number')
                            ->toArray();

                        if (empty($letterNumbers)) {
                            return 'Belum ada';
                        }

                        return implode(', ', $letterNumbers);
                    })
                    ->wrap()
                    ->size('sm'),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('year', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->outlined(),
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
            'index' => Pages\ListLetterCounters::route('/'),
            'edit' => Pages\EditLetterCounter::route('/{record}/edit'),
        ];
    }
}
