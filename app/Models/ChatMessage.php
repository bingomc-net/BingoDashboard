<?php

namespace App\Models;

use Filament\Forms\Components\Builder;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $connection = 'mysql_minecraft';
    protected $table = 'chat_messages';
    public $timestamps = false;

    protected $fillable = ['message', 'sender', 'server', 'sent_at', 'language', 'translation'];

    public function getAvatarUrlAttribute(): string
    {
        return "https://example.com/avatars/{$this->uuid}.png";
    }
    public function getShortTimeAttribute(): string
    {
        return \Carbon\Carbon::parse($this->sent_at)->format('H:i');
    }
    public function server()
    {
        return $this->belongsTo(BingoServer::class, 'server', 'id');
    }

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public static function tableQuery(): Builder
    {
        return parent::tableQuery()->with('sender');
    }


}
