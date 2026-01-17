<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerSession extends Model
{
    protected $connection = 'mysql_minecraft';

    protected $table = 'player_sessions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'session_start',
        'session_end',
        'minecraft_id',
        'client_version',
        'bungee_id',
        'connection_address',
        'country_code',
        'server_id',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'minecraft_id', 'uuid');
    }

}
