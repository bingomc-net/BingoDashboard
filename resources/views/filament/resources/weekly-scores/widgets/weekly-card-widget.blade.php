<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <x-filament::icon
                    icon="heroicon-o-puzzle-piece"
                    class="h-5 w-5 text-gray-400 dark:text-gray-500"
                />
                <span>Weekly Bingo Card</span>
            </div>
        </x-slot>

        <x-slot name="description">
            Current week's bingo items
        </x-slot>

        @php
            $items = $this->getWeeklyCard();
            $gridSize = $this->getCardGridSize();
            $nextWeekCardSet = $this->isNextWeekCardSet(); // You'll need to implement this method
        @endphp

        @if(empty($items))
            <div style="display: flex; align-items: center; justify-content: center; padding: 3rem 0; color: #9ca3af;">
                <div style="text-align: center;">
                    <x-filament::icon
                        icon="heroicon-o-puzzle-piece"
                        style="margin: 0 auto 0.75rem; height: 3rem; width: 3rem; opacity: 0.5;"
                    />
                    <p>No weekly card available yet</p>
                </div>
            </div>
        @else
            <div style="
                max-width: 42rem;
                margin: 0 auto;
                display: grid;
                grid-template-columns: repeat({{ $gridSize }}, minmax(0, 1fr));
                gap: 0.5rem;
            ">
                @foreach($items as $item)
                    <div style="
                        position: relative;
                        aspect-ratio: 1 / 1;
                        background-color: #18181b;
                        border-radius: 0.5rem;
                        overflow: hidden;
                        border: 2px solid #27272a;
                        transition: all 0.3s;
                    " onmouseover="this.style.borderColor='#f59e0b'; this.style.transform='scale(1.05)'; this.style.boxShadow='0 20px 25px -5px rgba(245, 158, 11, 0.2)';" onmouseout="this.style.borderColor='#27272a'; this.style.transform='scale(1)'; this.style.boxShadow='none';">
                        <div style="position: absolute; inset: 0; padding: 1rem; display: flex; align-items: center; justify-content: center;">
                            <img
                                src="/textures/{{ strtolower($item) }}.png"
                                alt="{{ $item }}"
                                style="width: 100%; height: 100%; object-fit: contain; image-rendering: pixelated; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.6));"
                            />
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="
                margin-top: 1.5rem;
                padding-top: 1rem;
                border-top: 1px solid #27272a;
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 0.875rem;
                color: #a1a1aa;
            ">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <x-filament::icon
                            icon="heroicon-o-square-3-stack-3d"
                            style="height: 1rem; width: 1rem;"
                        />
                        <span>{{ count($items) }} items</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <x-filament::icon
                            icon="heroicon-o-calendar"
                            style="height: 1rem; width: 1rem;"
                        />
                        <span>Week {{ now()->weekOfYear }}, {{ now()->year }}</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    @if($nextWeekCardSet)
                        <div style="
                            display: inline-flex;
                            align-items: center;
                            gap: 0.375rem;
                            padding: 0.25rem 0.75rem;
                            background-color: #064e3b;
                            color: #10b981;
                            border-radius: 9999px;
                            font-size: 0.75rem;
                            font-weight: 600;
                        ">
                            <x-filament::icon
                                icon="heroicon-s-check-circle"
                                style="height: 0.875rem; width: 0.875rem;"
                            />
                            <span>Next week ready</span>
                        </div>
                    @else
                        <div style="
                            display: inline-flex;
                            align-items: center;
                            gap: 0.375rem;
                            padding: 0.25rem 0.75rem;
                            background-color: #7c2d12;
                            color: #fb923c;
                            border-radius: 9999px;
                            font-size: 0.75rem;
                            font-weight: 600;
                        ">
                            <x-filament::icon
                                icon="heroicon-s-exclamation-circle"
                                style="height: 0.875rem; width: 0.875rem;"
                            />
                            <span>Next week not set</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
