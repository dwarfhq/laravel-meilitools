<x-pulse::card :cols="$cols" :rows="$rows" :class="$class">
    <x-pulse::card-header
        name="Meilisearch"
        x-bind:title="`Time: {{ number_format($time) }}ms; Run at: ${formatDate('{{ $runAt }}')};`"
        details="{{ $health['version'] === null ? 'unreachable' : 'v' . $health['version'] }}"
    >
        <x-slot:icon>
            <x-pulse::icons.circle-stack />
        </x-slot:icon>
    </x-pulse::card-header>

    <x-pulse::scroll :expand="$expand" wire:poll.30s="">
        @if ($health['version'] === null)
            <p class="text-sm font-bold text-red-600 dark:text-red-400">Meilisearch could not be reached.</p>
        @else
            <div class="flex flex-col gap-4">
                @foreach ($health['missingIndexes'] as $index)
                    <p class="text-sm text-red-600 dark:text-red-400">Index <code>{{ $index }}</code> is missing.</p>
                @endforeach
                @foreach ($health['errors'] as $index => $error)
                    <p class="text-sm text-red-600 dark:text-red-400">
                        Index <code>{{ $index }}</code> failed to synchronize: {{ $error }}
                    </p>
                @endforeach
                @foreach ($health['outOfSync'] as $index => $settings)
                    <p class="text-sm text-amber-600 dark:text-amber-400">
                        Index <code>{{ $index }}</code> has settings out of sync: {{ implode(', ', $settings) }}.
                    </p>
                @endforeach

                @if ($indexes === [])
                    <x-pulse::no-results />
                @else
                    <x-pulse::table>
                        <x-pulse::thead>
                            <tr>
                                <x-pulse::th>Index</x-pulse::th>
                                <x-pulse::th class="text-right">Documents</x-pulse::th>
                                <x-pulse::th class="text-right">Indexing</x-pulse::th>
                            </tr>
                        </x-pulse::thead>
                        <tbody>
                            @foreach ($indexes as $uid => $stats)
                                <tr wire:key="{{ $uid }}-spacer" class="h-2 first:h-0"></tr>
                                <tr wire:key="{{ $uid }}-row">
                                    <x-pulse::td>
                                        <code class="text-xs text-gray-900 dark:text-gray-100">{{ $uid }}</code>
                                    </x-pulse::td>
                                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300 font-bold">
                                        {{ number_format($stats['numberOfDocuments'] ?? 0) }}
                                    </x-pulse::td>
                                    <x-pulse::td numeric class="text-gray-700 dark:text-gray-300">
                                        {{ ($stats['isIndexing'] ?? false) ? 'Yes' : 'No' }}
                                    </x-pulse::td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-pulse::table>
                @endif

                @if ($health['failedTasks'] !== [])
                    <x-pulse::table>
                        <x-pulse::thead>
                            <tr>
                                <x-pulse::th>Failed task</x-pulse::th>
                                <x-pulse::th>Error</x-pulse::th>
                            </tr>
                        </x-pulse::thead>
                        <tbody>
                            @foreach ($health['failedTasks'] as $task)
                                <tr wire:key="task-{{ $task['uid'] }}-spacer" class="h-2 first:h-0"></tr>
                                <tr wire:key="task-{{ $task['uid'] }}-row">
                                    <x-pulse::td>
                                        <code class="text-xs text-gray-900 dark:text-gray-100">
                                            {{ $task['type'] ?? '' }}
                                        </code>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $task['indexUid'] ?? '' }} #{{ $task['uid'] }}
                                        </p>
                                    </x-pulse::td>
                                    <x-pulse::td class="text-xs text-red-600 dark:text-red-400">
                                        {{ $task['error']['message'] ?? '' }}
                                    </x-pulse::td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-pulse::table>
                @endif
            </div>
        @endif
    </x-pulse::scroll>
</x-pulse::card>
