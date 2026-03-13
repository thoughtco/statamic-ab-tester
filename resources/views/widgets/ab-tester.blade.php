<ui-widget title="{{ __('A/B Experiments') }}" icon="labs-idea-experimental-flask">
    @if ($experiments->isEmpty())
        <div class="flex flex-col items-center justify-center gap-2 py-10 text-center">
            <ui-description>{{ __('No experiments are currently running.') }}</ui-description>
            <a href="{{ $indexUrl }}" class="text-sm">{{ __('Create an experiment') }} &rarr;</a>
        </div>
    @else
        <ui-table class="px-4 mt-2">
            <ui-table-columns>
                <ui-table-column>{{ __('Experiment') }}</ui-table-column>
                <ui-table-column>{{ __('Hits') }}</ui-table-column>
                <ui-table-column>{{ __('Leader') }}</ui-table-column>
            </ui-table-columns>
            <ui-table-rows>
                @foreach ($experiments as $exp)
                    <ui-table-row>
                        <ui-table-cell>
                            <a href="{{ $exp['url'] }}" class="font-medium">{{ $exp['title'] }}</a>
                        </ui-table-cell>
                        <ui-table-cell>{{ number_format($exp['total_hits']) }}</ui-table-cell>
                        <ui-table-cell>
                            @if ($exp['leader_label'] !== null)
                                <span>{{ $exp['leader_label'] }}</span>
                                <ui-badge color="green" class="ml-2">{{ $exp['leader_rate'] }}%</ui-badge>
                            @else
                                <span>&mdash;</span>
                            @endif
                        </ui-table-cell>
                    </ui-table-row>
                @endforeach
            </ui-table-rows>
        </ui-table>

        <template #footer>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-right">
                <a href="{{ $indexUrl }}" class="text-sm">{{ __('View all experiments') }} &rarr;</a>
            </div>
        </template>
    @endif
</ui-widget>
