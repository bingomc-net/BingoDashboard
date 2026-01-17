<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerBalloon extends Model
{
    protected $connection = 'mysql_minecraft';
    protected $table = 'player_balloons';

    protected $fillable = [
        'player_uuid',
        'balloon_id',
        'collected_at',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_uuid', 'uuid');
    }
}
