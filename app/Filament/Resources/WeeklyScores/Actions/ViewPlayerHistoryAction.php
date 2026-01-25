<?php

namespace App\Filament\Resources\WeeklyScores\Actions;

use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use App\Services\BingoHistoryService;

class ViewPlayerHistoryAction
{
    public static function make(): Action
    {
        return Action::make('history')
            ->label('History')
            ->icon('heroicon-o-clock')
            ->color('info')
            ->modalHeading(fn ($record) => $record->name . "'s Best Attempt")
            ->modalContent(function ($record) {
                $service = app(BingoHistoryService::class);

                // Get current filters from the table
                $filters = request()->get('tableFilters', []);
                $period = $filters['period']['value'] ?? 'current_week';
                $gameType = $filters['game_type']['value'] ?? 'weekly';

                // Get all attempts for this player
                $attempts = $service->getPlayerAttempts($record->uuid, $period, $gameType);

                // Get only the best attempt (first one since it's sorted by fastest time)
                $bestAttempt = $attempts->first();

                if (!$bestAttempt) {
                    return new HtmlString('<p style="padding: 1rem; color: #9ca3af;">No attempts found for this period.</p>');
                }

                // Format and return HTML for just this one attempt
                return new HtmlString($service->formatSingleAttempt($bestAttempt));
            })
            ->modalSubmitAction(false)  // ← Hide submit button
            ->modalCancelAction(false)  // ← Hide cancel button
            ->slideOver();
    }
}
