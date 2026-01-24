<?php

namespace App\Filament\Resources\WeeklyScores\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WeeklyCardWidget extends Widget
{
    protected string $view = 'filament.resources.weekly-scores.widgets.weekly-card-widget';

    protected int|string|array $columnSpan = 1;

    public function getWeeklyCard(): array
    {
        // Get the current week number of the year
        $currentWeek = Carbon::now()->weekOfYear;

        // Try to get the specific weekly card for this week
        // Assuming weekly_card_1 is week 1, weekly_card_2 is week 2, etc.
        $settingKey = $currentWeek == 1 ? 'weekly_card' : 'weekly_card_' . $currentWeek;

        $setting = DB::connection('mysql_minecraft')
            ->table('settings')
            ->where('setting', $settingKey)
            ->first();

        if (!$setting || !$setting->value) {
            // Fallback to just 'weekly_card' if specific week not found
            $setting = DB::connection('mysql_minecraft')
                ->table('settings')
                ->where('setting', 'weekly_card')
                ->first();
        }

        if (!$setting || !$setting->value) {
            return [];
        }

        // The value is stored as a JSON array string
        return json_decode($setting->value, true) ?? [];
    }

    public function getCardGridSize(): int
    {
        $items = $this->getWeeklyCard();
        if (empty($items)) {
            return 0;
        }

        return (int)ceil(sqrt(count($items)));
    }
}
