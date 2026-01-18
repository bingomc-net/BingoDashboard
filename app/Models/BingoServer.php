<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BingoServer extends Model
{
    protected $connection = 'mysql_minecraft';

    protected $table = 'bingo_servers';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = ['id', 'ip', 'port', 'state', 'variant', 'players', 'restricted', 'bound_to', 'join_code', 'extra_data'];

    public static function query(): Builder
    {
        return parent::query()
            ->where('state', '!=', 'PLAYGROUND');
    }

    public function sessions()
    {
        return PlayerSession::where('server_id', 'game_' . $this->id)
            ->orWhere('server_id', 'lobby_' . $this->id);
    }

    public function onlinePlayers()
    {
        return PlayerSession::where(function($query) {
            $query->where('server_id', 'game_' . $this->id)
                ->orWhere('server_id', 'lobby_' . $this->id);
        })
            ->whereNull('session_end');
    }

    public function getChatMessagesAttribute()
    {
        return \App\Models\ChatMessage::where('server', 'game_' . $this->id)
            ->orWhere('server', 'lobby_' . $this->id)
            ->orderBy('sent_at', 'desc')
            ->get();
    }
    public function usernames()
    {
        return $this->hasMany(Player::class, 'server_id', 'id');
    }

    public function onlinePlayersWithPlayer()
    {
        return $this->onlinePlayers()->with('player');
    }

    public function allPlayersWithStats()
    {
        return PlayerSession::where(function($query) {
            $query->where('server_id', 'game_' . $this->id)
                ->orWhere('server_id', 'lobby_' . $this->id);
        })
            ->select(
                'minecraft_id',
                \DB::raw('MIN(session_start) as first_join'),
                \DB::raw('MAX(COALESCE(session_end, NOW())) as last_seen'),
                \DB::raw('COUNT(*) as total_sessions'),
                \DB::raw('SUM(TIMESTAMPDIFF(SECOND, session_start, COALESCE(session_end, NOW()))) as total_playtime')
            )
            ->groupBy('minecraft_id')
            ->orderBy('first_join', 'desc')
            ->get()
            ->map(function ($session) {
                $session->player_name = Player::getNameFromUuid($session->minecraft_id);
                $session->is_online = PlayerSession::where('minecraft_id', $session->minecraft_id)
                    ->where(function($query) {
                        $query->where('server_id', 'game_' . $this->id)
                            ->orWhere('server_id', 'lobby_' . $this->id);
                    })
                    ->whereNull('session_end')
                    ->exists();
                return $session;
            });
    }
}
