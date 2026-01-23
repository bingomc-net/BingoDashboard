<?php

namespace App\Filament\Resources\Chatlogs\Tables;

use App\Filament\Resources\Players\PlayerResource;
use App\Models\Player;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatlogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sender')
                    ->label('Sender')
                    ->formatStateUsing(function ($record) {
                        $player = Player::where('uuid', $record->sender)->first();
                        $avatarUrl = $player
                            ? "https://nmsr.nickac.dev/bust/" . ($player->uuid ?? "00000000-0000-0000-0000-000000000000")
                            : 'https://example.com/default-avatar.png';

                        $username = $record->sender
                            ? Player::getNameFromUuid($record->sender)
                            : 'Unknown';

                        return '<div style="display: flex; align-items: center; gap: 12px;">
                                    <img src="' . $avatarUrl . '" style="width: 40px; height: 40px; border-radius: 50%;" />
                                    <span>' . e($username) . '</span>
                                </div>';
                    })
                    ->html()
                    ->url(function ($record) {
                        $player = Player::where('uuid', $record->sender)->first();
                        if ($player) {
                            return PlayerResource::getUrl('view', ['record' => $player]);
                        }
                        return null;
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('message')
                    ->label('Message')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('server')
                    ->label('Server')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label('Sent At')
                    ->alignRight()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state
                        ? Carbon::parse($state)->format('M j, Y') . '<br>' . Carbon::parse($state)->format('H:i:s')
                        : null
                    )
                    ->html()
                    ->extraAttributes(['class' => 'text-sm']),
            ])
            ->paginationPageOptions([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
