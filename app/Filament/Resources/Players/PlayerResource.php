<?php

namespace App\Filament\Resources\Players;

use App\Filament\Resources\Players\Pages\CreatePlayer;
use App\Filament\Resources\Players\Pages\EditPlayer;
use App\Filament\Resources\Players\Pages\ListPlayers;
use App\Filament\Resources\Players\Pages\ViewPlayer;
use App\Filament\Resources\Players\Schemas\PlayerForm;
use App\Filament\Resources\Players\Schemas\PlayersInfolist;
use App\Filament\Resources\Players\Tables\PlayersTable;
use App\Models\Player;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Player';

    public static function form(Schema $schema): Schema
    {
        return PlayerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlayersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function countryFlagEmoji(string $countryCode): string
    {
        if (!$countryCode || strlen($countryCode) !== 2) {
            return '';
        }

        return mb_convert_encoding('&#' . (127397 + ord($countryCode[0])) . ';', 'UTF-8', 'HTML-ENTITIES') .
            mb_convert_encoding('&#' . (127397 + ord($countryCode[1])) . ';', 'UTF-8', 'HTML-ENTITIES');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlayers::route('/'),
            'create' => CreatePlayer::route('/create'),
            'edit' => EditPlayer::route('/{record}/edit'),
            'view' => ViewPlayer::route('/{record}'),
        ];
    }
}
