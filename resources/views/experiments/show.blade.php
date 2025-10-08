@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Experiments'), $experiment->title()))

@section('content')

    <ui-header title="{{ $experiment->title() }}" icon="labs-idea-experimental-flask">
        <ui-button href="{{ cp_route('ab.experiments.edit', $experiment->id()) }}" class="btn-primary">{{ __('Edit') }}</ui-button>
    </ui-header>

    <div>
        <ui-description class="mb-1">{{ __('Results') }}</ui-description>

        <p>TBC</p>

        <ab-experiment-results
          :initial='@json($experiment->results())'
          refresh-url="{{ cp_route('ab.experiments.results.show', $experiment->id()) }}"
        >
          <data-list slot-scope="{ results }" :columns='@json($columns)' :rows="results">
            <div class="card p-0" slot-scope="{ filteredRows: rows }">
              <data-list-table :rows="rows" />
            </div>
          </data-list>
        </ab-experiment-results>
    </div>
@stop
