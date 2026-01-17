<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        return $this->hasMany(PlayerSession::class, 'server_id', 'id');
    }

    public function onlinePlayers()
    {
        return $this->sessions()->whereNull('session_end');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'server_id');
    }

    public function usernames()
    {
        return $this->hasMany(Player::class, 'server_id', 'id');
    }
}
