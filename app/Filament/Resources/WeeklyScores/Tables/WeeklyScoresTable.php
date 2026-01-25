<?php

namespace App\Filament\Resources\WeeklyScores\Tables;

use App\Filament\Resources\WeeklyScores\Actions\ViewPlayerHistoryAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\WeeklyScores\Actions\ViewHistoryAction;

class WeeklyScoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->withoutGlobalScope('current_week')
                    ->select([
                        'bingo_stats_singleplayer.uuid',
                        DB::raw('MIN(TIMESTAMPDIFF(SECOND, game_start, game_end)) as time_seconds'),
                        DB::raw('MIN(game_end) as game_end'),
                        DB::raw('MAX(items_found) as items_found'),
                        DB::raw('MAX(game_type) as game_type'),
                    ])
                    ->whereNotNull('game_end')
                    ->whereNotNull('game_start')
                    ->groupBy('bingo_stats_singleplayer.uuid')
                    ->orderBy('time_seconds', 'asc');
            })
            ->columns([
                TextColumn::make('ranking')
                    ->label('')
                    ->state(function ($record, $rowLoop) {
                        $rank = $rowLoop->iteration;
                        return match($rank) {
                            1 => new HtmlString('<div style="font-size: 32px; text-align: center;">🥇</div>'),
                            2 => new HtmlString('<div style="font-size: 32px; text-align: center;">🥈</div>'),
                            3 => new HtmlString('<div style="font-size: 32px; text-align: center;">🥉</div>'),
                            default => new HtmlString('<div style="font-size: 20px; text-align: center; color: #9ca3af; font-weight: 600;">' . $rank . '</div>'),
                        };
                    })
                    ->width('80px')
                    ->alignCenter(),

                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->getStateUsing(fn ($record) =>
                        "https://nmsr.nickac.dev/bust/" . ($record->uuid ?? "00000000-0000-0000-0000-000000000000")
                    )
                    ->size(25),

                TextColumn::make('name')
                    ->label('IGN')
                    ->searchable()
                    ->url(fn ($record) => "https://namemc.com/profile/{$record->uuid}", shouldOpenInNewTab: true)
                    ->color('primary')
                    ->weight('bold')
                    ->tooltip('Click to view profile on NameMC'),

                TextColumn::make('time_seconds')
                    ->label('Time')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return 'N/A';

                        $hours = floor($state / 3600);
                        $minutes = floor(($state % 3600) / 60);
                        $seconds = $state % 60;

                        if ($hours > 0) {
                            return "{$hours}h {$minutes}m {$seconds}s";
                        } elseif ($minutes > 0) {
                            return "{$minutes}m {$seconds}s";
                        }

                        return "{$seconds}s";
                    })
                    ->badge()
                    ->color(fn ($record, $rowLoop) => match($rowLoop->iteration) {
                        1 => 'primary',
                        2 => 'info',
                        3 => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('game_end')
                    ->label('Completed')
                    ->dateTime('M j, g:i A'),
            ])
            ->actions([
                ViewPlayerHistoryAction::make(),
            ])
            ->filters([
                SelectFilter::make('period')
                    ->label('Time Period')
                    ->options([
                        'current_week' => 'Current Week',
                        'last_week' => 'Last Week',
                        'current_month' => 'Current Month',
                        'last_month' => 'Last Month',
                        'current_year' => 'Current Year',
                        'all_time' => 'All Time',
                    ])
                    ->default('current_week')
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value']) || $data['value'] === 'all_time') {
                            return $query;
                        }

                        return match($data['value']) {
                            'current_week' => $query->whereRaw('YEARWEEK(game_end, 1) = YEARWEEK(CURDATE(), 1)'),
                            'last_week' => $query->whereRaw('YEARWEEK(game_end, 1) = YEARWEEK(CURDATE(), 1) - 1'),
                            'current_month' => $query->whereRaw('YEAR(game_end) = YEAR(CURDATE()) AND MONTH(game_end) = MONTH(CURDATE())'),
                            'last_month' => $query->whereRaw('YEAR(game_end) = YEAR(CURDATE()) AND MONTH(game_end) = MONTH(CURDATE()) - 1'),
                            'current_year' => $query->whereRaw('YEAR(game_end) = YEAR(CURDATE())'),
                            default => $query,
                        };
                    }),

                SelectFilter::make('game_type')
                    ->label('Game Type')
                    ->options([
                        'weekly' => 'Weekly',
                        'daily' => 'Daily',
                        'custom' => 'Custom',
                    ])
                    ->default('weekly')
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            return $query->where('game_type', $data['value']);
                        }
                        return $query;
                    }),
            ])
            ->defaultSort('time_seconds', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}
