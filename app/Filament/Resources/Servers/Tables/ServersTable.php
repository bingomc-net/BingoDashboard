<?php

namespace App\Filament\Resources\Servers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->copyable()
                    ->copyMessage('Copied ID')
                    ->copyMessageDuration(1500),
                TextColumn::make('ip')
                    ->label('IP')
                    ->copyable()
                    ->copyMessage('copied IP')
                    ->copyMessageDuration(1500),
                TextColumn::make('port')
                    ->label('Port')
                    ->copyable()
                    ->copyMessage('Port copied')
                    ->copyMessageDuration(1500),
                TextColumn::make('variant')
                    ->label('Variant')
                    ->copyable()
                    ->copyMessage('Variant copied')
                    ->copyMessageDuration(1500),
                TextColumn::make('state')
                    ->label('State')
                    ->copyable()
                    ->copyMessage('State copied')
                    ->copyMessageDuration(1500),
                TextColumn::make('players')
                    ->label('Players')
                    ->copyable()
                    ->copyMessage('Players copied')
                    ->copyMessageDuration(1500),
                IconColumn::make('restricted')
                    ->label('Restricted')
                    ->boolean(),
                ImageColumn::make('bound_to')
                    ->label('Player')
                    ->getStateUsing(fn ($record) =>
                    $record->bound_to ? "https://nmsr.nickac.dev/bust/{$record->bound_to}" : null
                    )
                    ->size(25),
                TextColumn::make('join_code')
                    ->label('Join Code')
                    ->copyable()
                    ->copyMessage('copied Join Code')
                    ->copyMessageDuration(1500),
            ])
            ->defaultPaginationPageOption(25)

            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
