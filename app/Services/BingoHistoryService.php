<?php

namespace App\Services;

use App\Models\BingoStatSingleplayer;
use Illuminate\Support\Collection;

class BingoHistoryService
{
    /**
     * Get all attempts for a specific player UUID and time period
     */
    public function getPlayerAttempts(string $uuid, string $period = 'current_week', string $gameType = 'weekly'): Collection
    {
        $query = BingoStatSingleplayer::where('uuid', $uuid)
            ->whereNotNull('game_end')
            ->whereNotNull('game_start')
            ->where('game_type', $gameType);

        $query = match ($period) {
            'current_week'  => $query->whereRaw('YEARWEEK(game_end, 1) = YEARWEEK(CURDATE(), 1)'),
            'last_week'     => $query->whereRaw('YEARWEEK(game_end, 1) = YEARWEEK(CURDATE(), 1) - 1'),
            'current_month' => $query->whereRaw('YEAR(game_end) = YEAR(CURDATE()) AND MONTH(game_end) = MONTH(CURDATE())'),
            'last_month'    => $query->whereRaw('YEAR(game_end) = YEAR(CURDATE()) AND MONTH(game_end) = MONTH(CURDATE()) - 1'),
            'current_year'  => $query->whereRaw('YEAR(game_end) = YEAR(CURDATE())'),
            default         => $query,
        };

        return $query->orderBy('game_start', 'desc')->get();
    }

    /**
     * Format item/material name
     */
    private function formatItemName(string $item): string
    {
        return ucwords(strtolower(str_replace('_', ' ', $item)));
    }

    /**
     * Format a single attempt timeline (NO BOX, full height)
     */
    public function formatSingleAttempt($attempt): string
    {
        $extraStats = is_string($attempt->extra_stats)
            ? json_decode($attempt->extra_stats, true)
            : $attempt->extra_stats;

        if (!$extraStats) {
            return '<div style="padding: 1rem; color: #9ca3af;">No data available.</div>';
        }

        $timeline = [];

        foreach ($extraStats['item_times'] ?? [] as $entry) {
            $timeline[] = [
                'type' => 'item',
                'time' => $entry['time'],
                'item' => $entry['item'] ?? 'Unknown',
            ];
        }

        foreach ($extraStats['interactions'] ?? [] as $interaction) {
            $timeline[] = [
                'type' => 'interaction',
                'time' => $interaction['time'],
                'material' => $interaction['material'] ?? 'Unknown',
                'from' => $interaction['from_inventory'] ?? '',
                'to' => $interaction['to_inventory'] ?? '',
            ];
        }

        usort($timeline, fn ($a, $b) => $a['time'] <=> $b['time']);

        $html = '<div style="font-family: ui-sans-serif, system-ui, sans-serif; background: #09090b;">';

        // Header
        $html .= '
            <div style="padding: 2rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                <h2 style="margin: 0; font-size: 1.5rem; font-weight: 700;">Interaction Timeline</h2>
                <p style="margin-top: 0.5rem; opacity: 0.9;">' . count($timeline) . ' events</p>
            </div>
        ';

        // Timeline (FULL FLOW)
        $html .= '<div style="padding: 2rem; background: #18181b;">';

        foreach ($timeline as $i => $entry) {
            $time = '+' . round($entry['time'] / 1000, 1) . 's';
            $last = $i === array_key_last($timeline);

            $html .= '<div style="position: relative; padding-left: 3rem; padding-bottom: ' . ($last ? '0' : '2rem') . ';">';

            // Dot
            $html .= match ($entry['type']) {
                'item' =>
                '<div style="position:absolute; left:0; top:.4rem; width:1rem; height:1rem; background:#f59e0b; border-radius:9999px;"></div>',
                default =>
                '<div style="position:absolute; left:.25rem; top:.6rem; width:.5rem; height:.5rem; background:#52525b; border-radius:9999px;"></div>',
            };

            // Line
            if (!$last) {
                $html .= '<div style="position:absolute; left:.5rem; top:1.5rem; width:2px; height:100%; background:#27272a;"></div>';
            }

            // Content
            if ($entry['type'] === 'item') {
                $html .= '
                    <div style="background:#27272a; padding:1.25rem; border-radius:.75rem; border-left:3px solid #f59e0b;">
                        <div style="color:#f59e0b; font-weight:700;">' . $time . '</div>
                        <div style="margin-top:.5rem; color:#fafafa; font-weight:700;">🎯 Item found</div>
                        <div style="color:#a1a1aa; margin-top:.25rem;">' . htmlspecialchars($this->formatItemName($entry['item'])) . '</div>
                    </div>
                ';
            } else {
                $html .= '
                    <div>
                        <div style="color:#71717a; font-size:.875rem;">' . $time . '</div>
                        <div style="color:#fafafa; font-weight:600;">' . htmlspecialchars($this->formatItemName($entry['material'])) . '</div>
                        <div style="color:#71717a; font-size:.875rem;">' . htmlspecialchars($entry['from'] . ' → ' . $entry['to']) . '</div>
                    </div>
                ';
            }

            $html .= '</div>';
        }

        $html .= '</div></div>';

        return $html;
    }
}
