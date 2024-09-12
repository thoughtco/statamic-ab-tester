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
                class="warning"
                text="{{ __('Delete') }}"
                @click="confirmDeleteRow(experiment.slug, index)"
                v-if="experiment.deletable"
              ></dropdown-item>
            </dropdown-list>
          </template>
        </data-list-table>
      </div>
    </data-list>

  @else

    @include('statamic::partials.create-first', [
      'resource' => __('Experiment'),
      'description' => 'Stop guessing and start testing!',
      'svg' => 'empty/collection',
      'route' => cp_route('ab.experiments.create'),
    ])

  @endunless
@stop
