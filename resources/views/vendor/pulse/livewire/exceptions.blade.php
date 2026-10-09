<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header
        name="Exceptions"
        x-bind:title="`Time: {{ number_format($time) }}ms; Run at: ${formatDate('{{ $runAt }}')};`"
        details="past {{ $this->periodForHumans() }}"
    >
        <x-slot:icon>
            <x-pulse::icons.bug-ant />
        </x-slot:icon>
        <x-slot:actions>
            <x-pulse::select
                wire:model.live="orderBy"
                id="select-exceptions-order-by"
                label="Sort by"
                :options="[
                    'count' => 'count',
                    'latest' => 'latest',
                ]"
                @change="loading = true"
            />
        </x-slot:actions>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.5s="">
        @if ($exceptions->isEmpty())
            <x-pulse::no-results />
        @else
            <x-pulse::table>
                <colgroup>
                    <col width="100%" />
                    <col width="0%" />
                    <col width="0%" />
                    <col width="0%" />
                </colgroup>
                <x-pulse::thead>
                    <tr>
                        <x-pulse::th>Type</x-pulse::th>
                        <x-pulse::th class="text-right">Latest</x-pulse::th>
                        <x-pulse::th class="text-right">Count</x-pulse::th>
                        <x-pulse::th class="text-right">Trash</x-pulse::th>
                    </tr>
                </x-pulse::thead>
                <tbody>
                    @foreach ($exceptions->take(100) as $exception)
                        <tr wire:key="{{ $exception->class.$exception->location }}-spacer" class="h-2 first:h-0"></tr>
                        <tr wire:key="{{ $exception->class.$exception->location }}-row">
                            <x-pulse::td class="max-w-[1px]">
                                <code class="block text-xs text-gray-900 dark:text-gray-100 truncate" title="{{ $exception->class }}">
                                    {{ $exception->class }}
                                </code>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 truncate" title="{{ $exception->location }}">
                                    {{ $exception->location }}
                                </p>
                            </x-pulse::td>
                            <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                {{ $exception->latest->ago(syntax: Carbon\CarbonInterface::DIFF_ABSOLUTE, short: true) }}
                            </x-pulse::td>
                            <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                @if ($config['sample_rate'] < 1)
                                    <span title="Sample rate: {{ $config['sample_rate'] }}, Raw value: {{ number_format($exception->count) }}">~{{ number_format($exception->count * (1 / $config['sample_rate'])) }}</span>
                                @else
                                    {{ number_format($exception->count) }}
                                @endif
                            </x-pulse::td>
                            <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                <button title="Clear {{ $exception->class }} Exceptions at {{ $exception->location }}" x-bind="clearException" x-data="clearExceptionButton({className: @js($exception->class), location: @js($exception->location)})">
                                    <x-pulse::icons.trash class="w-5 h-5" />
                                </button>
                            </x-pulse::td>
                        </tr>
                    @endforeach
                </tbody>
            </x-pulse::table>
        @endif

        @if ($exceptions->count() > 100)
            <div class="mt-2 text-xs text-gray-400 text-center">Limited to 100 entries</div>
        @endif
    </x-pulse::scroll>
</x-pulse::card>

@script
<script>
    Alpine.data('clearExceptionButton', (config) => ({

        clearException: {
            ['x-ref']: 'clearException',
            ['@click']() {
                const className = config.className;
                const location = config.location;

                result = confirm('Are you sure you wish to delete all exceptions for '+className+' '+location+' ?');
                if (result) {
                    fetch("/errorz/clear/", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({className: className, location: location})
                    }).catch(err => {
                        // Silently fail to avoid infinite loops
                    });
                }
            },
        },
    }))
</script>
@endscript
