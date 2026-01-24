<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class WeeklyScore extends Model
{
    protected $connection = 'mysql_minecraft';
    protected $table = 'bingo_stats_singleplayer';

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
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

    protected $appends = ['name', 'formatted_time'];

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

    // Extra stats accessors
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

    // Time calculation
    public function getTimeAttribute()
    {
        // Check if time_seconds is already calculated by query
        if (isset($this->attributes['time_seconds'])) {
            return $this->attributes['time_seconds'];
        }

        if ($this->game_start && $this->game_end) {
            return $this->game_start->diffInSeconds($this->game_end);
        }

        return null;
    }

    public function getFormattedTimeAttribute()
    {
        $time = $this->time;
        if (!$time) return 'N/A';

        $hours = floor($time / 3600);
        $minutes = floor(($time % 3600) / 60);
        $seconds = $time % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m {$seconds}s";
        } elseif ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    public function getNameAttribute()
    {
        return $this->player?->name ?? Player::getNameFromUuid($this->uuid) ?? 'Unknown';
    }

    // Scope for best times (one per player)
    public function scopeBestTimes(Builder $query)
    {
        return $query->select([
            'uuid',
            DB::raw('MIN(TIMESTAMPDIFF(SECOND, game_start, game_end)) as best_time'),
            DB::raw('MIN(game_end) as game_end'),
        ])
            ->groupBy('uuid');
    }

    // Scope for current week
    public function scopeCurrentWeek(Builder $query)
    {
        return $query->whereRaw('YEARWEEK(game_end, 1) = YEARWEEK(CURDATE(), 1)');
    }

    // Scope for ranking
    public function scopeWithRanking(Builder $query)
    {
        return $query->select([
            '*',
            DB::raw('RANK() OVER (ORDER BY TIMESTAMPDIFF(SECOND, game_start, game_end) ASC) as ranking')
        ]);
    }
}
