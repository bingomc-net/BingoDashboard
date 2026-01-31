<div class="grid grid-cols-8 gap-2">
    @php
        $minecraftColors = [
            '&0' => '#000000', // Black
            '&1' => '#0000AA', // Dark Blue
            '&2' => '#00AA00', // Dark Green
            '&3' => '#00AAAA', // Dark Aqua
            '&4' => '#AA0000', // Dark Red
            '&5' => '#AA00AA', // Dark Purple
            '&6' => '#FFAA00', // Gold
            '&7' => '#AAAAAA', // Gray
            '&8' => '#555555', // Dark Gray
            '&9' => '#5555FF', // Blue
            '&a' => '#55FF55', // Green
            '&b' => '#55FFFF', // Aqua
            '&c' => '#FF5555', // Red
            '&d' => '#FF55FF', // Light Purple
            '&e' => '#FFFF55', // Yellow
            '&f' => '#FFFFFF', // White
        ];

        $formatCodes = [
            '&l' => 'Bold',
            '&m' => 'Strikethrough',
            '&n' => 'Underline',
            '&o' => 'Italic',
            '&r' => 'Reset',
        ];
    @endphp

    {{-- Color Buttons --}}
    @foreach($minecraftColors as $code => $hexColor)
        <button
            type="button"
            wire:click="addColorToMessage('{{ $code }}')"
            class="w-10 h-10 rounded border-2 border-gray-300 dark:border-gray-600 hover:scale-110 transition-transform"
            style="background-color: {{ $hexColor }}"
            title="{{ $code }}"
        >
            <span class="sr-only">{{ $code }}</span>
        </button>
    @endforeach

    {{-- Format Buttons --}}
    @foreach($formatCodes as $code => $label)
        <button
            type="button"
            wire:click="addColorToMessage('{{ $code }}')"
            class="w-10 h-10 rounded border-2 border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-xs font-semibold"
            title="{{ $label }} ({{ $code }})"
        >
            {{ substr($label, 0, 1) }}
        </button>
    @endforeach
</div>

<div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
    Click a color or format to add it to your message
</div>
