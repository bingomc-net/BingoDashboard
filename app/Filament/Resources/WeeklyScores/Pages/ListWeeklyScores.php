<?php

namespace App\Filament\Resources\WeeklyScores\Pages;

use App\Filament\Resources\WeeklyScores\WeeklyScoresResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListWeeklyScores extends ListRecords
{
    protected static string $resource = WeeklyScoresResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Weekly Bingo Leaderboard';
    }

    public function getHeading(): string|Htmlable
    {
        return 'Weekly Bingo Leaderboard';
    }

    protected function getHeaderActions(): array
    {
        return [
            // Add actions here if needed
        ];
    }

    public function getView(): string
    {
        return 'filament.resources.weekly-scores.pages.list-weekly-scores';
    }

    public function getActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
