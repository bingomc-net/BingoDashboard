<?php

namespace App\Filament\Resources\Chatlogs;

use App\Filament\Resources\Chatlogs\Pages\CreateChatlog;
use App\Filament\Resources\Chatlogs\Pages\EditChatlog;
use App\Filament\Resources\Chatlogs\Pages\ListChatlogs;
use App\Filament\Resources\Chatlogs\Pages\ViewChatlog;
use App\Filament\Resources\Chatlogs\Schemas\ChatlogForm;
use App\Filament\Resources\Chatlogs\Schemas\ChatlogInfolist;
use App\Filament\Resources\Chatlogs\Tables\ChatlogsTable;
use App\Models\ChatMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChatlogResource extends Resource
{
    protected static ?string $model = ChatMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Chatmessage';

    public static function form(Schema $schema): Schema
    {
        return ChatlogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ChatlogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatlogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatlogs::route('/'),
            'create' => CreateChatlog::route('/create'),
            'view' => ViewChatlog::route('/{record}'),
            'edit' => EditChatlog::route('/{record}/edit'),
        ];
    }
}
