<x-filament-panels::page>
    <div style="display: grid; grid-template-columns: 450px 1fr; gap: 1.5rem; align-items: start;">
        <div style="position: sticky; top: 1rem;">
            @livewire(\App\Filament\Resources\WeeklyScores\Widgets\WeeklyCardWidget::class)
        </div>
        <div>
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
