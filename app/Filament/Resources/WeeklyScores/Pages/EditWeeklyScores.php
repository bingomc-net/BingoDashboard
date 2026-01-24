<?php

namespace App\Filament\Resources\WeeklyScores\Pages;

use App\Filament\Resources\WeeklyScores\WeeklyScoresResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWeeklyScores extends EditRecord
{
    protected static string $resource = WeeklyScoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
