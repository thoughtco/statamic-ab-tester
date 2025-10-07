@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Experiments'), $experiment->title()))

@section('content')

  <publish-form
    title="{{ $experiment->title() }}"
    action="{{ cp_route('ab.experiments.update', $experiment->id()) }}"
    method="patch"
    :blueprint='@json($blueprint)'
    :values='@json($values)'
    :meta='@json($meta)'
  ></publish-form>
@stop
