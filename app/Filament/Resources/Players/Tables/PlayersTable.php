<?php

namespace App\Filament\Resources\Players\Tables;

use App\Filament\Resources\Players\PlayerResource;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class PlayersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->getStateUsing(fn ($record) =>
                        "https://nmsr.nickac.dev/bust/" . ($record->uuid ?? "00000000-0000-0000-0000-000000000000")
                    )
                    ->toggleable()
                    ->size(25),

                TextColumn::make('name')
                    ->weight('75%')
                    ->copyable()
                    ->copyMessage('Copied!')
                    ->copyMessageDuration(1500)
                    ->toggleable()
                    ->searchable()
                    ->copyableState(fn ($record): string => (string) $record->id),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        $latest = $record->sessions->sortByDesc('session_start')->first();
                        return $latest && $latest->session_end === null ? 'Online' : 'Offline';
                    })
                    ->formatStateUsing(fn ($state) => $state === 'Online' ? '🟢 Online' : '⚪ Offline')
                    ->toggleable(),

                TextColumn::make('uuid')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('country_code')
                    ->label('Country')
                    ->getStateUsing(fn ($record) => $record->latestSession?->country_code ?? 'Unknown')
                    ->formatStateUsing(function ($state) {
                        return Cache::remember("country_flag_{$state}", now()->addMinutes(10), function () use ($state) {
                            return ($state !== 'Unknown'
                                ? PlayerResource::countryFlagEmoji($state) . ' ' . strtoupper($state)
                                : 'Unknown');
                        });
                    })
                    ->toggleable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->primaryBadge['name'] ?? 'Member')
                    ->extraAttributes(fn ($record) => [
                        'class' => match($record->primaryBadge['color'] ?? 'slate') {
                            'red' => 'bg-red-500 text-white',
                            'black' => 'bg-black text-white',
                            'purple' => 'bg-purple-600 text-white',
                            'orange' => 'bg-orange-500 text-white',
                            'teal' => 'bg-teal-500 text-white',
                            'indigo' => 'bg-indigo-500 text-white',
                            'yellow' => 'bg-yellow-400 text-black',
                            'green' => 'bg-green-500 text-white',
                            'pink' => 'bg-pink-400 text-black',
                            'blue' => 'bg-blue-500 text-white',
                            'cyan' => 'bg-cyan-400 text-black',
                            default => 'bg-slate-500 text-white',
                        },
                    ]),

                TextColumn::make('created_at')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('online')
                    ->label('Online')
                    ->placeholder('All Players')
                    ->trueLabel('Online Only')
                    ->falseLabel('Offline Only')
                    ->queries(
                        true: fn ($query) => $query->whereHas('sessions', fn ($q) =>
                        $q->orderByDesc('session_start')->whereNull('session_end')
                        ),
                        false: fn ($query) => $query->whereHas('sessions', fn ($q) =>
                        $q->orderByDesc('session_start')->whereNotNull('session_end')
                        ),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
