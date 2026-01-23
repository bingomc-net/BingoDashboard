<?php

namespace App\Filament\Resources\Chatlogs\Pages;

use App\Filament\Resources\Chatlogs\ChatlogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditChatlog extends EditRecord
{
    protected static string $resource = ChatlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
