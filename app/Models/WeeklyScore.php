<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class WeeklyScore extends Model
{
    protected $connection = 'mysql_minecraft';
    protected $table = 'bingo_stats_singleplayer';

    // Use uuid as primary key for simplicity with Filament
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    // No timestamps columns
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'game_start',
        'game_end',
        'items_found',
        'game_settings',
        'game_type',
        'extra_stats',
    ];

    protected $casts = [
        'game_start' => 'datetime',
        'game_end' => 'datetime',
        'extra_stats' => 'array',
        'game_settings' => 'array',
    ];

    protected static function booted()
    {
        // Apply global scope to filter current week by default
        static::addGlobalScope('current_week', function (Builder $builder) {
            $builder->whereRaw('YEARWEEK(game_end, 1) = YEARWEEK(CURDATE(), 1)')
                ->where('game_type', 'weekly');
        });
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'uuid', 'uuid');
    }

    public function getItemTimesAttribute()
    {
        return $this->extra_stats['itemTimes'] ?? [];
    }

    public function getInteractionsAttribute()
    {
        return $this->extra_stats['interactions'] ?? [];
    }

    public function getItemsCheckedAttribute()
    {
        return $this->extra_stats['itemsChecked'] ?? [];
    }

    public function getItemsAttribute()
    {
        return $this->extra_stats['items'] ?? [];
    }

    public function getSeedAttribute()
    {
        return $this->extra_stats['seed'] ?? null;
    }

    public function getTimeAttribute()
    {
        if ($this->game_start && $this->game_end) {
            return $this->game_start->diffInSeconds($this->game_end);
        }

        return null;
    }

    public function getFormattedTimeAttribute()
    {
        $time = $this->time;
        if (!$time) return 'N/A';

        $minutes = floor($time / 60);
        $seconds = $time % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    public function getNameAttribute()
    {
        return $this->player?->name ?? Player::getNameFromUuid($this->uuid) ?? 'Unknown';
    }
}
