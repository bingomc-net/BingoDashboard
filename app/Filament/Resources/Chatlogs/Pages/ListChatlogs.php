<?php

namespace App\Filament\Resources\Chatlogs\Pages;

use App\Filament\Resources\Chatlogs\ChatlogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChatlogs extends ListRecords
{
    protected static string $resource = ChatlogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
