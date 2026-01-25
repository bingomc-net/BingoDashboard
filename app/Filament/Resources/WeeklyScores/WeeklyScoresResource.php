<?php

namespace App\Filament\Resources\WeeklyScores;

use App\Filament\Resources\WeeklyScores\Pages\CreateWeeklyScores;
use App\Filament\Resources\WeeklyScores\Pages\EditWeeklyScores;
use App\Filament\Resources\WeeklyScores\Pages\ListWeeklyScores;
use App\Filament\Resources\WeeklyScores\Pages\ViewWeeklyScores;
use App\Filament\Resources\WeeklyScores\Schemas\WeeklyScoresForm;
use App\Filament\Resources\WeeklyScores\Schemas\WeeklyScoresInfolist;
use App\Filament\Resources\WeeklyScores\Tables\WeeklyScoresTable;
use App\Models\WeeklyScore;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WeeklyScoresResource extends Resource
{
    protected static ?string $model = WeeklyScore::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'WeeklyScore';

    public static function form(Schema $schema): Schema
    {
        return WeeklyScoresForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WeeklyScoresInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeeklyScoresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public function isNextWeekCardSet(): bool
    {
        // Get next week's year and week number
        $nextWeek = now()->addWeek();
        $nextYear = $nextWeek->year;
        $nextWeekNumber = $nextWeek->weekOfYear;

        // Check if a card exists for next week
        // Adjust this query based on your database structure
        return \DB::connection('mysql_minecraft')
            ->table('weekly_cards') // Replace with your actual table name
            ->whereRaw('YEAR(week_start) = ?', [$nextYear])
            ->whereRaw('WEEK(week_start, 1) = ?', [$nextWeekNumber])
            ->exists();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWeeklyScores::route('/'),
            'create' => CreateWeeklyScores::route('/create'),
            'view' => ViewWeeklyScores::route('/{record}'),
            'edit' => EditWeeklyScores::route('/{record}/edit'),
        ];
    }
}
