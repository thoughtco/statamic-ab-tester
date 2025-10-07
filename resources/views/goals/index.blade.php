@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Experiments')))

@section('content')

    <ui-header title="{{ __('Goals') }}" icon="favorite-trophy">
        <ui-button variant="primary" text="{{ __('Create') }}" href="{{ cp_route('ab.goals.create') }}" />
    </ui-header>

    <ui-listing
        url="{{ cp_route('ab.goals.json') }}"
        :columns="{{ $columns }}"
        action-url="{{ cp_route('ab.goals.actions') }}"
    >
        <template #cell-title="{ row }">
            <a class="title-index-field" :href="row.edit_url" @click.stop>
                <span v-text="row.title" />
            </a>
        </template>
        <template #prepended-row-actions="{ row }">
            <p>Yo</p>
        </template>
    </ui-listing>

@stop
