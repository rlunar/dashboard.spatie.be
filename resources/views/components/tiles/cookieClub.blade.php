<x-dashboard-tile :position="$position" refresh-interval="60" :fade="false">
    @if($cookieOfTheWeek)
        <div class="flex flex-col h-full">
            <h2 class="font-bold text-center text-default">🍪 Cookie of the week</h2>

            <div class="grid grid-cols-2 gap-6 flex-1 min-h-0 mt-3">
                <div class="min-h-0 overflow-hidden rounded bg-canvas">
                    <img
                        src="{{ $cookieOfTheWeek['imageUrl'] }}"
                        alt="{{ $cookieOfTheWeek['name'] }}"
                        class="w-full h-full object-cover"
                    />
                </div>

                <section class="flex flex-col items-center justify-center min-w-0 leading-tight text-center">
                    <p class="font-bold text-default">{{ $cookieOfTheWeek['name'] }}</p>
                    <p class="mt-4 text-lg font-bold tabular-nums text-default">
                        {{ number_format($cookieOfTheWeek['score'], 2) }} <span class="text-warning">★</span>
                    </p>
                    <p class="mt-3 text-xs text-dimmed">Brought by {{ $cookieOfTheWeek['submittedBy'] }}</p>
                </section>
            </div>
        </div>
    @else
        <div class="flex items-center justify-center h-full text-sm text-dimmed">
            Cookie data unavailable
        </div>
    @endif
</x-dashboard-tile>
