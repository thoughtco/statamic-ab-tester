@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Experiments')))

@section('content')
  <ab-experiment-wizard
    route="{{ cp_route('ab.experiments.store') }}"
  ></ab-experiment-wizard>

{{--   @include('statamic::partials.breadcrumb', ['title' => __('Experiments'), 'url' => cp_route('ab.experiments.index')])

  <publish-form
    title="New Experiment"
    action="{{ cp_route('ab.experiments.store') }}"
    :blueprint='@json($blueprint)'
    :values='@json($values)'
    :meta='@json($meta)'
  ></publish-form> --}}
@stop