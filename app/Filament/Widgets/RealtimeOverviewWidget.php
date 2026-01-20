<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\LetterRequest;
use App\Enums\LetterRequestStatus;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RealtimeOverviewWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    /**
     * Poll interval for real-time updates.
     */
    protected ?string $pollingInterval = '10s';

    /**
     * Widget sort order on dashboard.
     */
    protected static ?int $sort = 1;

    /**
     * Number of columns for the widget.
     */
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            $this->getLettersTodayStat(),
            $this->getPendingRequestsStat(),
            $this->getCompletedThisMonthStat(),
            $this->getProcessingRateStat(),
        ];
    }

    /**
     * Letters created today with 7-day sparkline trend.
     */
    private function getLettersTodayStat(): Stat
    {
        $today = LetterRequest::whereDate('created_at', Carbon::today())->count();

        // Get last 7 days data for sparkline
        $sparklineData = $this->getLast7DaysData();

        // Calculate trend percentage
        $lastWeekAvg = count($sparklineData) > 1
            ? array_sum(array_slice($sparklineData, 0, -1)) / (count($sparklineData) - 1)
            : 0;
        $trend = $lastWeekAvg > 0
            ? round((($today - $lastWeekAvg) / $lastWeekAvg) * 100, 1)
            : 0;

        return Stat::make('Surat Hari Ini', (string) $today)
            ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color('primary')
            ->chart($sparklineData);
    }

    /**
     * Count of pending requests with warning threshold.
     */
    private function getPendingRequestsStat(): Stat
    {
        $pending = LetterRequest::where('status', LetterRequestStatus::Pending)->count();

        $sparklineData = $this->getStatusTrendData(LetterRequestStatus::Pending);

        return Stat::make('Menunggu Persetujuan', (string) $pending)
            ->chart($sparklineData);
    }

    /**
     * Completed requests this month with success rate.
     */
    private function getCompletedThisMonthStat(): Stat
    {
        $completedThisMonth = LetterRequest::where('status', LetterRequestStatus::Completed)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereYear('updated_at', Carbon::now()->year)
            ->count();

        $totalThisMonth = LetterRequest::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $successRate = $totalThisMonth > 0
            ? round(($completedThisMonth / $totalThisMonth) * 100, 1)
            : 0;

        $sparklineData = $this->getStatusTrendData(LetterRequestStatus::Completed);

        return Stat::make('Selesai Bulan Ini', (string) $completedThisMonth)
            ->chart($sparklineData);
    }

    /**
     * Current processing rate indicator.
     */
    private function getProcessingRateStat(): Stat
    {
        $processing = LetterRequest::where('status', LetterRequestStatus::Processing)->count();
        $total = LetterRequest::whereIn('status', [
            LetterRequestStatus::Pending,
            LetterRequestStatus::Processing,
        ])->count();

        $rate = $total > 0 ? round(($processing / $total) * 100, 1) : 0;

        $sparklineData = $this->getStatusTrendData(LetterRequestStatus::Processing);

        return Stat::make('Sedang Diproses', (string) $processing)
            ->chart($sparklineData);
    }

    /**
     * Get letter request counts for last 7 days.
     */
    private function getLast7DaysData(): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = LetterRequest::whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return $data;
    }

    /**
     * Get status-specific trend data for last 7 days.
     */
    private function getStatusTrendData(LetterRequestStatus $status): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = LetterRequest::where('status', $status)
                ->whereDate('created_at', $date)
                ->count();
            $data[] = $count;
        }

        return $data;
    }
}
