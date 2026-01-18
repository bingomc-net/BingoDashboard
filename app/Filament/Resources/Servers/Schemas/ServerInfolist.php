<?php

namespace App\Filament\Resources\Servers\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ServerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Server Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('ip')
                                    ->label('IP Address')
                                    ->copyable()
                                    ->icon('heroicon-o-server'),

                                TextEntry::make('port')
                                    ->label('Port')
                                    ->icon('heroicon-o-hashtag'),

                                TextEntry::make('state')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'RUNNING' => 'success',
                                        'STARTING' => 'warning',
                                        'STOPPED' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('variant')
                                    ->label('Game Variant')
                                    ->icon('heroicon-o-puzzle-piece'),

                                TextEntry::make('players')
                                    ->label('Player Count')
                                    ->icon('heroicon-o-users'),

                                TextEntry::make('restricted')
                                    ->label('Restricted')
                                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'warning' : 'success'),

                                TextEntry::make('server_uptime')
                                    ->label('Server Uptime')
                                    ->getStateUsing(function ($record) {
                                        $earliestSession = $record->onlinePlayers()
                                            ->orderBy('session_start', 'asc')
                                            ->first();

                                        if (!$earliestSession) {
                                            return 'No active sessions';
                                        }

                                        return \Carbon\Carbon::parse($earliestSession->session_start)
                                            ->diffForHumans(null, true);
                                    })
                                    ->icon('heroicon-o-clock')
                                    ->badge()
                                    ->color('info'),
                            ]),

                        Grid::make(1)
                            ->schema([
                                TextEntry::make('join_code')
                                    ->label('Join Code')
                                    ->copyable()
                                    ->copyMessage('Copied Join Code')
                                    ->copyMessageDuration(1500)
                                    ->icon('heroicon-o-key')
                                    ->placeholder('No join code')
                                    ->badge()
                                    ->color('primary'),
                            ]),
                    ])
                    ->columns(1)
                    ->icon('heroicon-o-server'),

                Tabs::make('Server Details')
                    ->tabs([
                        Tabs\Tab::make('Players')
                            ->icon('heroicon-o-signal')
                            ->badge(fn ($record) => $record->onlinePlayers()->count())
                            ->schema([
                                RepeatableEntry::make('onlinePlayersData')
                                    ->hiddenLabel()
                                    ->schema([
                                        Grid::make([
                                            'default' => 6,
                                        ])
                                            ->schema([
                                                ImageEntry::make('minecraft_id')
                                                    ->label('')
                                                    ->hiddenLabel()
                                                    ->getStateUsing(fn ($record) => "https://mc-heads.net/avatar/{$record->minecraft_id}/64")
                                                    ->size(64)
                                                    ->columnSpan(1),

                                                TextEntry::make('player_name')
                                                    ->label('')
                                                    ->hiddenLabel()
                                                    ->weight('bold')
                                                    ->size('lg')
                                                    ->url(fn ($record) => "/staff/players/{$record->minecraft_id}")
                                                    ->color('primary')
                                                    ->columnSpan(2),

                                                TextEntry::make('session_duration')
                                                    ->label('')
                                                    ->hiddenLabel()
                                                    ->getStateUsing(fn ($record) =>
                                                    \Carbon\Carbon::parse($record->session_start)->diffForHumans(null, true)
                                                    )
                                                    ->badge()
                                                    ->color('success')
                                                    ->columnSpan(1),
                                            ]),
                                    ])
                                    ->contained(true)
                                    ->getStateUsing(function ($record) {
                                        $onlinePlayers = $record->onlinePlayers()
                                            ->orderBy('session_start', 'desc')
                                            ->get();

                                        if ($onlinePlayers->isEmpty()) {
                                            return [];
                                        }

                                        return $onlinePlayers->map(function ($session) {
                                            $session->player_name = \App\Models\Player::getNameFromUuid($session->minecraft_id) ?? 'Unknown';
                                            return $session;
                                        });
                                    }),
                            ]),

                        Tabs\Tab::make('Chat Logs')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->badge(function ($record) {
                                return \App\Models\ChatMessage::where(function($query) use ($record) {
                                    $query->where('server', 'game_' . $record->id)
                                        ->orWhere('server', 'lobby_' . $record->id);
                                })->count();
                            })
                            ->schema([
                                RepeatableEntry::make('recentChatMessages')
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
                                                                \App\Models\Player::getNameFromUuid($record->sender) ?? 'Unknown'
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
                                        return \App\Models\ChatMessage::where(function($query) use ($record) {
                                            $query->where('server', 'game_' . $record->id)
                                                ->orWhere('server', 'lobby_' . $record->id);
                                        })
                                            ->orderBy('sent_at', 'desc')
                                            ->limit(50)
                                            ->get();
                                    }),
                            ]),
                    ]),
            ]);
    }
}
