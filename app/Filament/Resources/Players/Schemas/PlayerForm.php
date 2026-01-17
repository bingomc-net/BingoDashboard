<?php

namespace App\Filament\Resources\Players\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use App\Filament\Resources\Players\PlayerResource;

class PlayerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->disabled()
                            ->default(fn($player) => $player->name),

                        TextInput::make('uuid')
                            ->label('Minecraft UUID')
                            ->disabled()
                            ->default(fn($player) => $player->uuid),

                        TextInput::make('country_code')
                            ->label('Country')
                            ->disabled()
                            ->default(fn($record) => $record->latestSession?->country_code ?? 'Unknown')
                            ->formatStateUsing(function ($state) {
                                return Cache::remember("country_flag_{$state}", now()->addMinutes(10), function () use ($state) {
                                    return ($state !== 'Unknown' && $state !== null
                                        ? PlayerResource::countryFlagEmoji($state) . ' ' . strtoupper($state)
                                        : 'Unknown');
                                });
                            }),
                    ]),
            ]);
    }
}
