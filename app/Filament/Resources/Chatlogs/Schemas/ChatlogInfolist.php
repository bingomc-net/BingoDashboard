<?php

namespace App\Filament\Resources\Chatlogs\Schemas;

use App\Models\Player;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class ChatlogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->icon('heroicon-o-user-circle')
                    ->columns(3)
                    ->schema([
                        ImageEntry::make('sender')
                            ->label('Avatar')
                            ->getStateUsing(fn ($record) => "https://mc-heads.net/avatar/{$record->sender}/128")
                            ->size(80)
                            ->columnSpan(1),

                        TextEntry::make('sender_name')
                            ->label('Player Name')
                            ->state(fn ($record) => Player::getNameFromUuid($record->sender) ?? 'Unknown')
                            ->url(fn ($record) => "/staff/players/{$record->sender}")
                            ->color('primary')
                            ->weight('bold')
                            ->size(TextSize::Large)
                            ->columnSpan(1),

                        TextEntry::make('sent_at')
                            ->label('Sent At')
                            ->dateTime('M d, H:i')
                            ->icon('heroicon-o-clock')
                            ->badge()
                            ->color('gray')
                            ->columnSpan(1),

                        TextEntry::make('server_info.ip')
                            ->label('IP Address')
                            ->getStateUsing(function ($record) {
                                $serverId = str_replace(['game_', 'lobby_'], '', $record->server);
                                $server = \App\Models\BingoServer::find($serverId);
                                return $server?->ip ?? 'N/A';
                            })
                            ->copyable()
                            ->icon('heroicon-o-server'),

                        TextEntry::make('server_info.port')
                            ->label('Port')
                            ->getStateUsing(function ($record) {
                                $serverId = str_replace(['game_', 'lobby_'], '', $record->server);
                                $server = \App\Models\BingoServer::find($serverId);
                                return $server?->port ?? 'N/A';
                            })
                            ->icon('heroicon-o-hashtag'),

                        TextEntry::make('server_info.state')
                            ->label('Server State')
                            ->getStateUsing(function ($record) {
                                $serverId = str_replace(['game_', 'lobby_'], '', $record->server);
                                $server = \App\Models\BingoServer::find($serverId);
                                return $server?->state ?? 'UNKNOWN';
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'RUNNING' => 'success',
                                'STARTING' => 'warning',
                                'STOPPED' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('server_info.variant')
                            ->label('Game Variant')
                            ->getStateUsing(function ($record) {
                                $serverId = str_replace(['game_', 'lobby_'], '', $record->server);
                                $server = \App\Models\BingoServer::find($serverId);
                                return $server?->variant ?? 'N/A';
                            })
                            ->icon('heroicon-o-puzzle-piece'),

                        TextEntry::make('server_info.players')
                            ->label('Player Count')
                            ->getStateUsing(function ($record) {
                                $serverId = str_replace(['game_', 'lobby_'], '', $record->server);
                                $server = \App\Models\BingoServer::find($serverId);
                                return $server?->players ?? '0';
                            })
                            ->icon('heroicon-o-users'),

                        TextEntry::make('server_info.restricted')
                            ->label('Restricted')
                            ->getStateUsing(function ($record) {
                                $serverId = str_replace(['game_', 'lobby_'], '', $record->server);
                                $server = \App\Models\BingoServer::find($serverId);
                                return $server?->restricted ? 'Yes' : 'No';
                            })
                            ->badge()
                            ->color(fn ($state) => $state === 'Yes' ? 'warning' : 'success'),
                    ]),

                Section::make('Chat messages')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        RepeatableEntry::make('serverMessages')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        Grid::make([
                                            'default' => 6,
                                        ])
                                            ->schema([
                                                ImageEntry::make('sender')
                                                    ->label('')
                                                    ->hiddenLabel()
                                                    ->getStateUsing(fn ($record) => "https://mc-heads.net/avatar/{$record->sender}/32")
                                                    ->size(32)
                                                    ->circular()
                                                    ->columnSpan(1),

                                                TextEntry::make('sender')
                                                    ->label('')
                                                    ->hiddenLabel()
                                                    ->weight('medium')
                                                    ->getStateUsing(fn ($record) =>
                                                        Player::getNameFromUuid($record->sender) ?? 'Unknown'
                                                    )
                                                    ->url(fn ($record) => "/staff/players/{$record->sender}")
                                                    ->color('primary')
                                                    ->columnSpan(2),

                                                TextEntry::make('sent_at')
                                                    ->label('')
                                                    ->hiddenLabel()
                                                    ->dateTime('M d, H:i')
                                                    ->icon('heroicon-o-clock')
                                                    ->iconColor('gray')
                                                    ->size('sm')
                                                    ->columnSpan(2),
                                            ]),

                                        TextEntry::make('message')
                                            ->label('')
                                            ->hiddenLabel()
                                            ->formatStateUsing(fn ($state) => nl2br(e($state)))
                                            ->html()
                                            ->columnSpanFull()
                                            ->extraAttributes(['class' => 'text-sm ml-10 mt-2 text-gray-300']),
                                    ]),
                            ])
                            ->contained(true)
                            ->getStateUsing(function ($record) {
                                return \App\Models\ChatMessage::where('server', $record->server)
                                    ->orderBy('sent_at', 'desc')
                                    ->limit(50)
                                    ->get();
                            }),
                    ]),
            ]);
    }
}
