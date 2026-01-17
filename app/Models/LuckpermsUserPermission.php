<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LuckpermsUserPermission extends Model
{
    protected $connection = 'mysql_luckperms';
    protected $table = 'luckperms_user_permissions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    public static function renamePermissions(): array
    {
        return [
            'group.owner' => ['name' => 'Owner', 'color' => 'black', 'priority' => 1],
            'group.admin' => ['name' => 'Admin', 'color' => 'red', 'priority' => 2],
            'group.developer' => ['name' => 'Developer', 'color' => 'purple', 'priority' => 3],
            'group.moderator' => ['name' => 'Moderator', 'color' => 'orange', 'priority' => 4],
            'group.staff' => ['name' => 'Staff', 'color' => 'teal', 'priority' => 5],
            'group.partner' => ['name' => 'Partner', 'color' => 'indigo', 'priority' => 6],
            'group.builder' => ['name' => 'Builder', 'color' => 'yellow', 'priority' => 7],
            'group.eventhost' => ['name' => 'Event Host', 'color' => 'green', 'priority' => 8],
            'group.marketeer' => ['name' => 'Marketing', 'color' => 'pink', 'priority' => 9],
            'group.beta' => ['name' => 'Beta Tester', 'color' => 'blue', 'priority' => 10],
            'group.privategames' => ['name' => 'Private Games', 'color' => 'cyan', 'priority' => 11],
            'group.default' => ['name' => 'Member', 'color' => 'slate', 'priority' => 99],
        ];
    }

}
