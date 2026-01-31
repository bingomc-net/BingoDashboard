<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as ViewComponent;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Http;

class Announcement extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.announcement';

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-speaker-wave';

    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Announcements';
    }

    public function mount(): void
    {
        $this->form->fill([
            'message_type' => '&l&7[&l&aBingoMC&l&7]&7',
            'send_to'      => 'all_lobby',
            'message'      => '',
        ]);
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Section::make('Message Type')->schema([
                        Radio::make('message_type')
                            ->options([
                                '&l&7[&l&aBingoMC&l&7]&7' => 'BingoMC',
                                '&7&l[&c&lAlert&7&l]&7'   => 'Alert',
                                '&7&l[&b&lEvent&7&l]&7'   => 'Event',
                            ])
                            ->required()
                            ->label(false),
                    ]),

                    Section::make('Chat Color')->schema([
                        ViewComponent::make('color_grid')
                            ->view('filament.partials.minecraft-color-grid'),
                    ]),

                    Section::make('Sent To')->schema([
                        Radio::make('send_to')
                            ->options([
                                'all_online' => 'Online',
                                'all_lobby'  => 'Lobby',
                                'player'     => 'Player',
                            ])
                            ->live()
                            ->required()
                            ->label(''),

                        Select::make('player_uuid')
                            ->placeholder('Select a player...')
                            ->searchable()
                            ->options(function (): array {
                                if (class_exists(\App\Models\Player::class)) {
                                    return \App\Models\Player::query()->pluck('name', 'uuid')->all();
                                }

                                return [];
                            })
                            ->visible(fn ($get) => $get('send_to') === 'player')
                            ->label(''),
                    ]),
                ]),

                Section::make('Chat')
                    ->schema([
                        TextInput::make('message')
                            ->placeholder('Type your message here...')
                            ->required()
                            ->live(),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function addColorToMessage(string $colorCode): void
    {
        $this->data['message'] = ((string) ($this->data['message'] ?? '')) . $colorCode;
    }

    public function sendMessage(): void
    {
        $data = $this->form->getState();

        $message = trim((string) ($data['message'] ?? ''));
        if ($message === '') {
            Notification::make()
                ->danger()
                ->title('Message cannot be empty')
                ->send();

            return;
        }

        try {
            $payload = [
                'prefix'  => $data['message_type'],
                'message' => $data['message'],
            ];

            if (($data['send_to'] ?? null) !== 'player') {
                $payload['recipients'] = $data['send_to'];
            } else {
                $payload['player'] = $data['player_uuid'] ?? null;
            }

            Http::post('/api/announcements', $payload);

            Notification::make()
                ->success()
                ->title('Announcement sent')
                ->send();

            $this->data['message'] = '';
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->danger()
                ->title('Something went wrong')
                ->body('Check logs for details')
                ->send();
        }
    }
}
