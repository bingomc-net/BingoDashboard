<?php

namespace App\Filament\Widgets;

use App\Models\Player;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlayerStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalPlayers = Player::count();

        // This week
        $thisWeekStart = now()->startOfWeek();
        $thisWeekEnd = now()->endOfWeek();
        $thisWeekPlayers = Player::whereBetween('created_at', [$thisWeekStart, $thisWeekEnd])->count();

        // Last week
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();
        $lastWeekPlayers = Player::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

        // Calculate difference
        $difference = $thisWeekPlayers - $lastWeekPlayers;
        $percentageChange = $lastWeekPlayers > 0
            ? round((($thisWeekPlayers - $lastWeekPlayers) / $lastWeekPlayers) * 100, 1)
            : 0;

        // Online players
        $onlinePlayers = Player::whereHas('sessions', function ($query) {
            $query->whereNull('session_end')
                ->where('session_start', '>=', now()->subHours(24));
        })->count();

        // Monthly stats
        $thisMonthPlayers = Player::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Today's registrations
        $todayPlayers = Player::whereDate('created_at', now()->today())->count();

        return [
            Stat::make('Total Players', number_format($totalPlayers))
                ->description($difference >= 0
                    ? "+{$difference} from last week ({$percentageChange}%)"
                    : "{$difference} from last week ({$percentageChange}%)")
                ->descriptionIcon($difference >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($difference >= 0 ? 'success' : 'danger')
                ->chart($this->getWeeklyTrend()),

            Stat::make('Online Now', number_format($onlinePlayers))
                ->description('Active in last 24h')
                ->descriptionIcon('heroicon-m-signal')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),

            Stat::make('This Month', number_format($thisMonthPlayers))
                ->description($todayPlayers . ' joined today')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }

    protected function getWeeklyTrend(): array
    {
        $trend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = Player::whereDate('created_at', $date)->count();
            $trend[] = $count;
        }

        return $trend;
    }

    protected ?string $pollingInterval = '20s';
}
