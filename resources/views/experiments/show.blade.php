@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Experiments'), $experiment->title()))

@section('content')
  <header class="mb-3">
    @include('statamic::partials.breadcrumb', ['title' => __('Experiments'), 'url' => cp_route('ab.experiments.index')])

    <div class="flex items-center justify-between mb-3">
      <h1 class="flex-1">{{ $experiment->title() }}</h1>

      <a href="{{ cp_route('ab.experiments.edit', $experiment->handle()) }}" class="btn-primary">{{ __('Edit') }}</a>
    </div>
  </header>

  <div>
    <h2 class="mb-1">{{ __('Results') }}</h2>

    <ab-experiment-results
      :initial='@json($experiment->results())'
      refresh-url="{{ cp_route('ab.experiments.results.show', $experiment->handle()) }}"
    >
      <data-list slot-scope="{ results }" :columns='@json($columns)' :rows="results">
        <div class="card p-0" slot-scope="{ filteredRows: rows }">
          <data-list-table :rows="rows" />
        </div>
      </data-list>
    </ab-experiment-results>
  </div>
@stop
