@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Experiments')))

@section('content')
  @unless($experiments->isEmpty())

    <div class="flex items-center justify-between mb-3">
      <h1 class="flex-1">{{ __('A/B Experiments') }}</h1>

      <a href="{{ cp_route('ab.experiments.create') }}" class="btn-primary">{{ __('Create Experiment') }}</a>
    </div>

    <data-list :columns='@json($columns)' :rows='@json($experiments)'>
      <div class="card p-0" slot-scope="{ filteredRows: rows }">
        <data-list-table :rows="rows">
          <template slot="cell-title" slot-scope="{ row: experiment }">
            <a :href="experiment.url" v-text="experiment.title" />
          </template>
          <template slot="actions" slot-scope="{ row: experiment, index }">
            <dropdown-list>
              <dropdown-item :text="__('Edit')" :redirect="experiment.edit_url"></dropdown-item>
                <dropdown-item
                    :text="__('Delete')"
                    class="warning"
                    @click="$refs[`deleter_${experiment.handle}`].confirm()"
                >
                    <resource-deleter
                        :ref="`deleter_${experiment.handle}`"
                        :resource="experiment"
                        @deleted="location.reload()">
                    </resource-deleter>
                </dropdown-item>
            </dropdown-list>
          </template>
        </data-list-table>
      </div>
    </data-list>

  @else
      <header class="py-8 mt-8 text-center starting-style-transition" v-cloak>
          <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-3">
              <span>A/B Experiments</span>
          </h1>
      </header>

      <ui-empty-state-menu :heading="__('Add your first experiment with these easy steps')">
          <ui-empty-state-item
              :href="editUrl"
              icon="favorite-trophy"
              :heading="__('Add a goal')"
              :description="__('statamic::messages.collection_next_steps_configure_description')"
          />
          <ui-empty-state-item
              :href="editUrl"
              icon="labs-idea-experimental-flask"
              :heading="__('Add an A/B version')"
              :description="__('statamic::messages.collection_next_steps_configure_description')"
          />
      </ui-empty-state-menu>

  @endunless
@stop
