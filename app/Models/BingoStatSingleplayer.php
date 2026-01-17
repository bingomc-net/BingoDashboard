<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BingoStatSingleplayer extends Model
{
    protected $connection = 'mysql_minecraft';
    protected $table = 'bingo_stats_singleplayer';

    protected $fillable = [
        'uuid',
        'items_found',
        'game_start',
        'game_end',
    ];

    protected $casts = [
        'game_start' => 'datetime',
        'game_end' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'uuid', 'uuid');
    }
}
