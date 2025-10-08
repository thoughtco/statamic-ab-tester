@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Experiments')))

@section('content')

    <ui-header title="{{ __('Goals') }}" icon="favorite-trophy">
        <ui-button variant="primary" text="{{ __('Create') }}" href="{{ cp_route('ab.goals.create') }}" />
    </ui-header>

    <ui-listing
        url="{{ cp_route('ab.goals.json') }}"
        action-url="{{ cp_route('ab.goals.actions') }}"
        preferences-prefix="ab.goals"
    >
        <template #cell-title="{ row }">
            <a class="title-index-field" :href="row.edit_url" @click.stop>
                <span v-text="row.title" />
            </a>
        </template>
        <template #prepended-row-actions="{ row }">
            <ui-dropdown-item :text="__('View')" :href="row.show_url" icon="eye" />
            <ui-dropdown-item :text="__('Edit')" :href="row.edit_url" icon="edit" />
        </template>
    </ui-listing>

@stop
