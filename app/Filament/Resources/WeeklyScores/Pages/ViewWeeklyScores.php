<?php

namespace App\Filament\Resources\WeeklyScores\Pages;

use App\Filament\Resources\WeeklyScores\WeeklyScoresResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWeeklyScores extends ViewRecord
{
    protected static string $resource = WeeklyScoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
