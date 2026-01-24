<?php

namespace App\Models;

use Filament\Forms\Components\Builder;
use Illuminate\Database\Eloquent\Model;

class ChatMessageBlocked extends Model
{
    protected $connection = 'mysql_minecraft';
    protected $table = 'chat_messages_blocked';
    public $timestamps = false;

    protected $fillable = ['id', 'pattern',];
}
