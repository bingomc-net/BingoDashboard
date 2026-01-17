<?php

namespace App\Filament\Resources\Players\Pages;

use App\Filament\Resources\Players\PlayerResource;
use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\DB;

class ViewPlayer extends ViewRecord
{
    protected static string $resource = PlayerResource::class;

    public int $chatPage = 1;

    public function getTitle(): string
    {
        return $this->record->name ?? 'View player';
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make()
                    ->schema([
                        Section::make('Player Overview')
                            ->icon('heroicon-o-user')
                            ->headerActions([
                                Action::make('status')
                                    ->label(function ($record) {
                                        $online = $record->sessions->sortByDesc('session_start')->first()?->session_end === null;
                                        return $online ? 'Online' : 'Offline';
                                    })
                                    ->badge()
                                    ->icon(function ($record) {
                                        $online = $record->sessions->sortByDesc('session_start')->first()?->session_end === null;
                                        return $online ? 'heroicon-s-signal' : 'heroicon-s-signal-slash';
                                    })
                                    ->color(function ($record) {
                                        $online = $record->sessions->sortByDesc('session_start')->first()?->session_end === null;
                                        return $online ? 'success' : 'gray';
                                    }),
                            ])
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        ImageEntry::make('avatar')
                                            ->label('')
                                            ->getStateUsing(fn ($record) =>
                                                "https://nmsr.nickac.dev/bust/" . ($record->uuid ?? "00000000-0000-0000-0000-000000000000")
                                            )
                                            ->hiddenLabel()
                                            ->size('20rem'),

                                        Group::make([
                                            TextEntry::make('name')
                                                ->label('Username')
                                                ->weight(FontWeight::Bold)
                                                ->copyable(),

                                            TextEntry::make('uuid')
                                                ->label('UUID')
                                                ->copyable(),

                                            TextEntry::make('discordConnection.discord_id')
                                                ->label('Discord ID')
                                                ->copyable()
                                                ->getStateUsing(fn ($record) => $record->discordConnection?->discord_id ?? 'Not connected')
                                                ->prefixAction(
                                                    fn ($record) => $record->discordConnection?->discord_id
                                                        ? Action::make('view')
                                                            ->url('https://discord.com/users/' . $record->discordConnection->discord_id, true)
                                                            ->icon('heroicon-o-arrow-top-right-on-square')
                                                        : null
                                                ),

                                            TextEntry::make('groups')
                                                ->label('Groups')
                                                ->getStateUsing(function ($record) {
                                                    $roles = $record->permissions
                                                        ->pluck('permission')
                                                        ->filter(fn ($perm) => str_starts_with($perm, 'group.'))
                                                        ->unique()
                                                        ->values();

                                                    $map = \App\Models\LuckpermsUserPermission::renamePermissions();

                                                    return $roles->map(function ($role) use ($map) {
                                                        if (!isset($map[$role])) {
                                                            return null;
                                                        }

                                                        $info = $map[$role];
                                                        $class = match($info['color']) {
                                                            'red' => 'bg-red-500 text-white',
                                                            'black' => 'bg-black text-white',
                                                            'purple' => 'bg-purple-600 text-white',
                                                            'orange' => 'bg-orange-500 text-white',
                                                            'teal' => 'bg-teal-500 text-white',
                                                            'indigo' => 'bg-indigo-500 text-white',
                                                            'yellow' => 'bg-yellow-400 text-black',
                                                            'green' => 'bg-green-500 text-white',
                                                            'pink' => 'bg-pink-400 text-black',
                                                            'blue' => 'bg-blue-500 text-white',
                                                            'cyan' => 'bg-cyan-400 text-black',
                                                            default => 'bg-slate-500 text-white',
                                                        };

                                                        return "<span class='inline-block text-sm font-semibold rounded px-2 py-1 {$class}'>{$info['name']}</span>";
                                                    })->filter()->implode(' ');
                                                })
                                                ->html(),

                                        ])->columns(1),

                                        Group::make([
                                            TextEntry::make('created_at')
                                                ->label('First Joined')
                                                ->dateTime(),

                                            TextEntry::make('last_joined')
                                                ->label('Last Joined')
                                                ->getStateUsing(fn ($record) =>
                                                optional($record->sessions->sortByDesc('session_start')->first())->session_start
                                                )
                                                ->dateTime(),

                                            TextEntry::make('server')
                                                ->label('Server')
                                                ->getStateUsing(fn ($record) =>
                                                    optional($record->sessions->sortByDesc('session_start')->first())->getAttribute('server') ?? 'N/A'
                                                )
                                                ->url(fn ($record) =>
                                                optional($record->sessions->sortByDesc('session_start')->first())->session_end === null
                                                    ? '/server/' . (optional($record->sessions->sortByDesc('session_start')->first())->getAttribute('server') ?? '')
                                                    : null
                                                )
                                                ->openUrlInNewTab(),

                                            TextEntry::make('country_code_raw')
                                                ->label('Country Code')
                                                ->getStateUsing(fn ($record) => strtoupper($record->latestSession?->country_code ?? 'N/A')),
                                        ])->columns(1),
                                    ]),
                            ])
                            ->columnSpan(3)
                            ->extraAttributes(['class' => 'h-full']),

                        Section::make('Punishments')
                            ->icon('heroicon-o-shield-exclamation')
                            ->schema([
                                TextEntry::make('total_bans')->label('Total Bans')->badge()->color('danger')
                                    ->getStateUsing(fn ($record) => DB::connection('mysql_litebans_bingo')->table('litebans_bans')->where('uuid', $record->uuid)->count()),
                                TextEntry::make('total_kicks')->label('Total Kicks')->badge()->color('warning')
                                    ->getStateUsing(fn ($record) => DB::connection('mysql_litebans_bingo')->table('litebans_kicks')->where('uuid', $record->uuid)->count()),
                                TextEntry::make('total_mutes')->label('Total Mutes')->badge()->color('info')
                                    ->getStateUsing(fn ($record) => DB::connection('mysql_litebans_bingo')->table('litebans_mutes')->where('uuid', $record->uuid)->count()),
                                TextEntry::make('total_warnings')->label('Total Warnings')->badge()->color('gray')
                                    ->getStateUsing(fn ($record) => DB::connection('mysql_litebans_bingo')->table('litebans_warnings')->where('uuid', $record->uuid)->count()),
                                TextEntry::make('last_punished')
                                    ->hiddenLabel()
                                    ->getStateUsing(function ($record) {
                                        $lastBan = DB::connection('mysql_litebans_bingo')
                                            ->table('litebans_bans')
                                            ->where('uuid', $record->uuid)
                                            ->orderBy('time', 'desc')
                                            ->value('time');

                                        $lastKick = DB::connection('mysql_litebans_bingo')
                                            ->table('litebans_kicks')
                                            ->where('uuid', $record->uuid)
                                            ->orderBy('time', 'desc')
                                            ->value('time');

                                        $lastMute = DB::connection('mysql_litebans_bingo')
                                            ->table('litebans_mutes')
                                            ->where('uuid', $record->uuid)
                                            ->orderBy('time', 'desc')
                                            ->value('time');

                                        $lastWarning = DB::connection('mysql_litebans_bingo')
                                            ->table('litebans_warnings')
                                            ->where('uuid', $record->uuid)
                                            ->orderBy('time', 'desc')
                                            ->value('time');

                                        $times = array_filter([$lastBan, $lastKick, $lastMute, $lastWarning]);

                                        if (empty($times)) {
                                            return 'Last Punish: Never';
                                        }

                                        $mostRecent = max($times);
                                        return 'Last Punish: ' . date('M d, Y H:i', $mostRecent / 1000);
                                    }),
                            ])
                            ->columnSpan(1)
                            ->extraAttributes(['class' => 'h-full']),
                    ])
                    ->columns(4)
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'items-stretch']),

                Group::make()
                    ->schema([
                        Section::make('Statistics')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                KeyValueEntry::make('bingo_stats_multiplayer')
                                    ->label('Multiplayer Stats')
                                    ->getStateUsing(fn ($record) => $record->getBingoStatsSummary()['multiplayer'] ?? []),
                                KeyValueEntry::make('bingo_stats_singleplayer')
                                    ->label('Singleplayer Stats')
                                    ->getStateUsing(fn ($record) => $record->getBingoStatsSummary()['singleplayer'] ?? []),
                                KeyValueEntry::make('bingo_stats_extra')
                                    ->label('Extra Stats')
                                    ->getStateUsing(fn ($record) => $record->getBingoStatsSummary()['extra'] ?? []),
                            ])
                            ->columnSpan(1),

                        Section::make('Commands')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Actions::make([
                                    Action::make('previousPage')->label('<')->disabled(fn ($livewire) => $livewire->chatPage <= 1)->action(fn ($livewire) => $livewire->chatPage--),
                                    Action::make('nextPage')->label('>')->action(fn ($livewire) => $livewire->chatPage++)
                                        ->disabled(function ($record, $livewire) {
                                            $totalMessages = $record->chatMessages()->count();
                                            $totalPages = (int) ceil($totalMessages / 8);
                                            return $livewire->chatPage >= $totalPages;
                                        }),
                                ]),
                                RepeatableEntry::make('chatMessages')
                                    ->hiddenLabel()
                                    ->getStateUsing(fn ($record, $livewire) => $record->chatMessages()->skip((($livewire->chatPage ?? 1) - 1) * 8)->take(8)->get()->values())
                                    ->schema([
                                        TextEntry::make('message')
                                            ->hiddenLabel()
                                            ->formatStateUsing(function ($state, $record) {
                                                $message = nl2br(e($state));
                                                $time = optional($record->sent_at)?->format('H:i') ?? '--:--';
                                                $server = $record->getAttribute('server') ?? 'N/A';

                                                return "
                                                    <div class='flex items-start justify-between gap-3'>
                                                        <div class='flex-1 text-sm text-white'>{$message}</div>
                                                        <div class='flex items-center gap-2 flex-shrink-0'>
                                                            <span class='text-xs text-gray-500'>{$time}</span>
                                                            <span class='text-xs text-gray-500'>{$server}</span>
                                                        </div>
                                                    </div>
                                                ";
                                            })
                                            ->html()
                                            ->extraAttributes(['class' => 'p-2 bg-gray-800/30 rounded border border-gray-700/50 hover:bg-gray-800/50 transition']),
                                    ])
                                    ->extraAttributes(['class' => 'space-y-1.5']),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
