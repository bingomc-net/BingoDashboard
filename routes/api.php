<?php

Route::get('/weekly', function () {
    $latestScore = DB::connection('mysql_minecraft')
        ->table('bingo_stats_singleplayer')
        ->whereRaw('YEARWEEK(game_end, 1) = YEARWEEK(CURDATE(), 1)')
        ->where('game_type', 'weekly')
        ->orderBy('game_start', 'desc')
        ->first();

    if (!$latestScore || !$latestScore->extra_stats) {
        return response()->json([]);
    }

    $extraStats = is_string($latestScore->extra_stats)
        ? json_decode($latestScore->extra_stats, true)
        : $latestScore->extra_stats;

    return response()->json($extraStats['items'] ?? []);
});
