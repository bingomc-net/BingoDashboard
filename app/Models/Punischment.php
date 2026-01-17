<?php

namespace App\Models;

use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Model;

class Punischment extends Model
{
    protected $connection = 'mysql_litebans_bingo';
    protected $table = 'punishments';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'ip',
        'reason',
        'banned_by_uuid',
        'banned_by_name',
        'removed_by_uuid',
        'removed_by_name',
        'removed_by_reason',
        'removed_by_date',
        'time',
        'until',
        'template',
        'server_scope',
        'server_origin',
        'silent',
        'ipban',
        'ipban_wildcard',
        'active',
    ];

    protected $casts = [
        'time' => 'datetime',
        'until' => 'datetime',
        'removed_by_date' => 'datetime',
        'silent' => 'boolean',
        'ipban' => 'boolean',
        'ipban_wildcard' => 'boolean',
        'active' => 'boolean',
    ];
}
