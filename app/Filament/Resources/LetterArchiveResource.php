<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterArchiveResource\Pages;
use App\Models\LetterRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LetterArchiveResource extends Resource
{
    protected static ?string $model = LetterRequest::class;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-archive-box';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }

    public static function getNavigationLabel(): string
    {
        return 'Arsip Surat';
    }

    public static function getModelLabel(): string
    {
        return 'Arsip Surat';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Arsip Surat';
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return 'arsip-surat';
    }

    public static function getEloquentQuery(): Builder
    {
        // Only show completed letters with generated numbers
        return parent::getEloquentQuery()
            ->where('status', 'completed')
            ->whereNotNull('letter_number');
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('letter_number')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->copyable(),
                TextColumn::make('letterType.name')
                    ->label('Jenis Surat')
                    ->sortable()
                    ->badge(),
                TextColumn::make('user.name')
                    ->label('Pembuat')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Diarsipkan Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Dari Tanggal'),
                        DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->recordActions([
                \Filament\Actions\Action::make('download')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->outlined()
                    ->url(fn(LetterRequest $record) => route('download.letter', $record)),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLetterArchives::route('/'),
        ];
    }
}
