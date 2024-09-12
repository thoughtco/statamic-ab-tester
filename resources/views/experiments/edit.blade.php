@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Experiments'), $experiment->title()))

@section('content')
  @include('statamic::partials.breadcrumb', ['title' => __('Back'), 'url' => cp_route('ab.experiments.show', $experiment->handle())])

  <publish-form
    title="{{ $experiment->title() }}"
    action="{{ cp_route('ab.experiments.update', $experiment->handle()) }}"
    method="patch"
    :blueprint='@json($blueprint)'
    :values='@json($values)'
    :meta='@json($meta)'
  ></publish-form>
@stop
