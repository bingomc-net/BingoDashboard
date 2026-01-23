<?php

namespace App\Filament\Resources\Chatlogs\Pages;

use App\Filament\Resources\Chatlogs\ChatlogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewChatlog extends ViewRecord
{
    protected static string $resource = ChatlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
