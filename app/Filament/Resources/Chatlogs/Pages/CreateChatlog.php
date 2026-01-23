<?php

namespace App\Filament\Resources\Chatlogs\Pages;

use App\Filament\Resources\Chatlogs\ChatlogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChatlog extends CreateRecord
{
    protected static string $resource = ChatlogResource::class;
}
