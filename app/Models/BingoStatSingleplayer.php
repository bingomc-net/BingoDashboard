<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BingoStatSingleplayer extends Model
{
    protected $connection = 'mysql_minecraft';
    protected $table = 'bingo_stats_singleplayer';

    protected $fillable = [
        'uuid',
        'game_start',
        'game_end',
        'items_found',
        'game_type',
        'extra_stats', // or whatever the column name is
    ];

    protected $casts = [
        'game_start' => 'datetime',
        'game_end' => 'datetime',
        'extra_stats' => 'array', // This will automatically decode JSON
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'uuid', 'uuid');
    }

    // Get timeline sorted by time
    public function getTimelineAttribute()
    {
        if (!$this->extra_stats) {
            return [];
        }

        $interactions = $this->extra_stats['interactions'] ?? [];
        $itemTimes = $this->extra_stats['itemTimes'] ?? [];

        $timeline = [];

        // Add interactions to timeline
        foreach ($interactions as $interaction) {
            $timeline[] = [
                'type' => 'interaction',
                'time' => $interaction['time'],
                'material' => $interaction['material'],
                'fromInventory' => $interaction['fromInventory'],
                'toInventory' => $interaction['toInventory'],
            ];
        }

        // Add item times to timeline
        foreach ($itemTimes as $entry) {
            $timeline[] = [
                'type' => 'itemTime',
                'time' => $entry['time'],
                'item' => $entry['item'],
            ];
        }

        // Sort by time
        usort($timeline, fn($a, $b) => $a['time'] <=> $b['time']);

        return $timeline;
    }

    // Get just the items collected
    public function getItemsCollectedAttribute()
    {
        if (!$this->extra_stats) {
            return [];
        }

        $itemTimes = $this->extra_stats['itemTimes'] ?? [];

        return collect($itemTimes)->map(function ($entry) {
            return [
                'item' => $entry['item'],
                'time' => $entry['time'],
            ];
        })->sortBy('time')->values()->all();
    }
}
