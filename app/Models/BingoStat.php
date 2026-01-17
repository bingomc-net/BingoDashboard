<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BingoStat extends Model
{
    protected $connection = 'mysql_minecraft';
    protected $table = 'bingo_stats';

    protected $fillable = [
        'uuid',
        'items_found',
        'game_start',
        'game_end',
        'game_won',
    ];

    protected $casts = [
        'game_start' => 'datetime',
        'game_end' => 'datetime',
        'game_won' => 'boolean',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'uuid', 'uuid');
    }
}
