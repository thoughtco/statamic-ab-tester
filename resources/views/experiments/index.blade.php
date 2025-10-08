@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Experiments')))

@section('content')
  @unless($experimentsIsEmpty)

    <ui-header title="{{ __('A/B Experiments') }}" icon="labs-idea-experimental-flask" />

    <ui-listing
        url="{{ cp_route('ab.experiments.json') }}"
        action-url="{{ cp_route('ab.experiments.actions') }}"
        preferences-prefix="ab.experiments"
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

  @else
      <header class="py-8 mt-8 text-center starting-style-transition" v-cloak>
          <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
              <span>A/B Experiments</span>
          </h1>
      </header>

      <ui-empty-state-menu :heading="__('Add your first experiment with these easy steps')">
          <ui-empty-state-item
              href="{{ cp_route('ab.goals.create') }}"
              icon="favorite-trophy"
              :heading="__('Add a goal')"
              :description="__('statamic::messages.collection_next_steps_configure_description')"
          />
          <ui-empty-state-item
              icon="labs-idea-experimental-flask"
              :heading="__('Add an A/B version')"
              :description="__('statamic::messages.collection_next_steps_configure_description')"
          />
      </ui-empty-state-menu>

  @endunless
@stop
