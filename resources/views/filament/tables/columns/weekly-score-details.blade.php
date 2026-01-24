<div style="padding: 1rem;">
    <div style="margin-bottom: 1rem;">
        <h3 style="font-weight: 600; margin-bottom: 0.5rem;">Items Breakdown</h3>
    </div>

    @php
        $itemsChecked = $getRecord()->items_checked;
        $extraStats = json_decode($getRecord()->extra_stats, true);
        $itemStats = $extraStats['item_stats'] ?? [];
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.75rem;">
        @foreach($itemStats as $item => $stats)
            <div style="
                background-color: rgba(55, 65, 81, 0.3);
                border-radius: 0.5rem;
                padding: 0.75rem;
                border: 1px solid rgba(75, 85, 99, 0.5);
            ">
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <img
                        src="/textures/{{ strtolower($item) }}.png"
                        alt="{{ $item }}"
                        style="width: 24px; height: 24px; image-rendering: pixelated;"
                    />
                    <span style="font-weight: 500; font-size: 0.875rem;">{{ $item }}</span>
                </div>

                <div style="font-size: 0.75rem; color: #9ca3af;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span>Time:</span>
                        <span style="font-weight: 600;">{{ gmdate('H:i:s', $stats['time']) }}</span>
                    </div>
                    @if(isset($stats['count']))
                        <div style="display: flex; justify-content: space-between;">
                            <span>Count:</span>
                            <span style="font-weight: 600;">{{ $stats['count'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
