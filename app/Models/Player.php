<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $connection = 'mysql_minecraft';
    protected $table = 'players';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = ['uuid', 'name', 'language'];

    public function permissions()
    {
        return $this->hasMany(LuckpermsUserPermission::class, 'uuid', 'uuid')
            ->where('permission', 'like', 'group.%');
    }

    public function announcements()
    {
        return $this->hasMany(PlayerAnnouncement::class, 'player_uuid', 'uuid');
    }

    public static function getAllPlayers()
    {
        return static::all();
    }

    public function getBadgeAttribute(): ?array
    {
        $roles = $this->permissions
            ->pluck('permission')
            ->filter(fn ($perm) => str_starts_with($perm, 'group.'))
            ->toArray();

        $map = LuckpermsUserPermission::renamePermissions();

        $highest = collect($roles)
            ->filter(fn ($perm) => isset($map[$perm]))
            ->sortBy(fn ($perm) => $map[$perm]['priority'])
            ->first();

        return $highest
            ? ['name' => $map[$highest]['name'], 'color' => $map[$highest]['color']]
            : ['name' => 'No Role', 'color' => 'slate'];
    }

    public function sessions()
    {
        return $this->hasMany(PlayerSession::class, 'minecraft_id', 'uuid');
    }

    public function latestSession()
    {
        return $this->hasOne(PlayerSession::class, 'minecraft_id', 'uuid')
            ->orderByDesc('session_start');
    }

    public static function getNameFromUuid(string $uuid): ?string
    {
        return static::query()
            ->where('uuid', $uuid)
            ->value('name');
    }

    public function discordConnection()
    {
        return $this->hasOne(DiscordConnections::class, 'minecraft_id');
    }

    public function getPrimaryBadgeAttribute(): ?array
    {
        $roles = $this->permissions
            ->pluck('permission')
            ->filter(fn ($perm) => str_starts_with($perm, 'group.'))
            ->toArray();

        $map = \App\Models\LuckpermsUserPermission::renamePermissions();

        $highest = collect($roles)
            ->filter(fn ($perm) => isset($map[$perm]))
            ->sortBy(fn ($perm) => $map[$perm]['priority'])
            ->first();

        return $highest
            ? ['name' => $map[$highest]['name'], 'color' => $map[$highest]['color']]
            : ['name' => 'Member', 'color' => 'slate'];
    }

    public function bingoStats()
    {
        return $this->hasMany(\App\Models\BingoStat::class, 'uuid', 'uuid');
    }

    public function bingoStatsSingleplayer()
    {
        return $this->hasMany(BingoStatSingleplayer::class, 'uuid', 'uuid');
    }

    public function playerBalloons()
    {
        return $this->hasMany(PlayerBalloon::class, 'player_uuid', 'uuid');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender', 'uuid')->orderBy('sent_at', 'desc');
    }

    public function scopeLatestSent($query)
    {
        return $query->orderBy('sent_at', 'desc');
    }

    protected function getMonthlyGrowth(): int
    {
        return Player::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function server()
    {
        return $this->belongsTo(BingoServer::class, 'server', 'id');
    }

    public function getServerAttribute()
    {
        return $this->attributes['server'] ?? null;
    }

    public function punishments()
    {
        return $this->hasMany(Punischment::class, 'player_id');
    }

    public function getIsOnlineAttribute(): bool
    {
        $latest = $this->sessions()->orderByDesc('session_start')->first();
        return $latest && $latest->session_end === null;
    }

    public function getLastSessionAttribute()
    {
        return $this->latestSession?->session_start;
    }

    public function getBingoStatsSummary(): array
    {
        $formatDuration = fn ($seconds) => $seconds
            ? gmdate('i:s', (int) $seconds)
            : 'N/A';

        $avgItemsMulti = $this->bingoStats()->avg('items_found');
        $avgItemsSingle = $this->bingoStatsSingleplayer()->avg('items_found');

        $avgTimeMulti = $this->bingoStats()
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, game_start, game_end)) as avg_duration')
            ->value('avg_duration');

        $avgTimeSingle = $this->bingoStatsSingleplayer()
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, game_start, game_end)) as avg_duration')
            ->value('avg_duration');

        $firstGameMulti = $this->bingoStats()->orderBy('game_start')->value('game_start');
        $firstGameSingle = $this->bingoStatsSingleplayer()->orderBy('game_start')->value('game_start');

        $fastestTimeSingle = $this->bingoStatsSingleplayer()
            ->selectRaw('MIN(TIMESTAMPDIFF(SECOND, game_start, game_end)) as min_duration')
            ->value('min_duration');

        $totalOnlineTime = $this->sessions()
            ->selectRaw('SUM(TIMESTAMPDIFF(SECOND, session_start, session_end)) as total_duration')
            ->value('total_duration');

        return [
            'multiplayer' => [
                'Avg Items per Game' => number_format($avgItemsMulti ?? 0, 2),
                'Avg Time per Game' => $formatDuration($avgTimeMulti),
                'First Multiplayer Game' => $firstGameMulti ? $firstGameMulti->format('Y-m-d H:i') : 'N/A',
                'Total Multiplayer Games' => $this->bingoStats()->count(),
                'Games Won' => $this->bingoStats()->where('game_won', true)->count(),
                'Total Items Found' => $this->bingoStats()->sum('items_found'),
            ],
            'singleplayer' => [
                'Avg Items per Game' => number_format($avgItemsSingle ?? 0, 2),
                'Avg Time to Complete Card' => $formatDuration($avgTimeSingle),
                'Fastest Time' => $formatDuration($fastestTimeSingle),
                'First Singleplayer Game' => $firstGameSingle ? $firstGameSingle->format('Y-m-d H:i') : 'N/A',
                'Total Singleplayer Games' => $this->bingoStatsSingleplayer()->count(),
                'Total Items Found' => $this->bingoStatsSingleplayer()->sum('items_found'),
            ],
            'extra' => [
                'Balloons Collected' => $this->playerBalloons()->count(),
                'Total Online Time' => sprintf('%d days, %d hours (%d total hours)', intdiv($totalOnlineTime, 86400), intdiv($totalOnlineTime % 86400, 3600), intdiv($totalOnlineTime, 3600)),
            ],
        ];
    }
}
