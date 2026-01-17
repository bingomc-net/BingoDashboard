<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordConnections extends Model
{
    protected $connection = 'mysql_minecraft';
    protected $table = 'discord_connection';

    protected $primaryKey = 'discord_id';
    public $timestamps = false;

    protected $fillable = ['minecraft_id', 'discord_id'];


    public function getAvatarUrlAttribute(): string
    {
        return "https://cdn.discordapp.com/avatars/{$this->discord_id}/{$this->server_icon}";
    }
    public function discordConnection()
    {
        return $this->hasOne(DiscordConnections::class, 'minecraft_id', 'uuid');
    }
}
