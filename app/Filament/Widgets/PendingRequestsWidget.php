<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\LetterRequestStatus;
use App\Filament\Resources\LetterRequestResource;
use App\Models\LetterRequest;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingRequestsWidget extends BaseWidget
{
    /**
     * Widget sort order on dashboard.
     */
    protected static ?int $sort = 3;

    /**
     * Number of columns for the widget.
     */
    protected int|string|array $columnSpan = 'full';

    /**
     * Widget heading.
     */
    protected static ?string $heading = 'Pending Requests';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('letterType.name')
                    ->label('Letter Type')
                    ->icon('heroicon-o-document-text')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(LetterRequestStatus $state): string => $state->getLabel())
                    ->color(fn(LetterRequestStatus $state): string => match ($state) {
                        LetterRequestStatus::Pending => 'warning',
                        LetterRequestStatus::Processing => 'info',
                        LetterRequestStatus::Completed => 'success',
                        LetterRequestStatus::Rejected => 'danger',
                    })
                    ->icon(fn(LetterRequestStatus $state): string => match ($state) {
                        LetterRequestStatus::Pending => 'heroicon-m-clock',
                        LetterRequestStatus::Processing => 'heroicon-m-arrow-path',
                        LetterRequestStatus::Completed => 'heroicon-m-check-circle',
                        LetterRequestStatus::Rejected => 'heroicon-m-x-circle',
                    }),

                Tables\Columns\TextColumn::make('letter_number')
                    ->label('Letter Number')
                    ->placeholder('Not assigned yet')
                    ->color('gray')
                    ->toggleable(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(
                        fn(LetterRequest $record): string =>
                        LetterRequestResource::getUrl('view', ['record' => $record])
                    ),
            ])
            ->emptyStateHeading('No pending requests')
            ->emptyStateDescription('You have no letter requests awaiting action.')
            ->emptyStateIcon('heroicon-o-document-check')
            ->striped()
            ->paginated([5, 10, 25]);
    }

    /**
     * Query for current user's non-completed requests.
     */
    protected function getTableQuery(): Builder
    {
        $query = LetterRequest::query()
            ->whereIn('status', [
                LetterRequestStatus::Pending,
                LetterRequestStatus::Processing,
            ])
            ->with('letterType');

        // Users only see their own requests
        if (!auth()->user()?->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }
}
