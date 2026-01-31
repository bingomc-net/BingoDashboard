<x-filament-panels::page>
    <form wire:submit.prevent="sendMessage" class="space-y-6 md:p-6">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button
                type="submit"
                color="success"
                outlined=" "
                icon="heroicon-o-paper-airplane"
                icon-position="after"
            >
                Send
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
