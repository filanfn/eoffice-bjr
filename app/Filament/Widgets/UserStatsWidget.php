<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\LetterRequestStatus;
use App\Models\LetterRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    /**
     * Poll interval for real-time updates.
     */
    protected ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        // Only visible to regular users (non-admins)
        return !auth()->user()?->isAdmin();
    }

    protected function getStats(): array
    {
        $userId = auth()->id();

        $totalSubmitted = LetterRequest::where('user_id', $userId)->count();

        $completed = LetterRequest::where('user_id', $userId)
            ->where('status', LetterRequestStatus::Completed)
            ->count();

        $rejected = LetterRequest::where('user_id', $userId)
            ->where('status', LetterRequestStatus::Rejected)
            ->count();

        return [
            Stat::make('My Submissions', (string) $totalSubmitted),
            Stat::make('Approvals', (string) $completed),
            Stat::make('Rejected', (string) $rejected),
        ];
    }
}
