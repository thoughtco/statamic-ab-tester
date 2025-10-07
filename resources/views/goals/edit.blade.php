@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Experiments'), $experiment->title()))

@section('content')

    <ui-publish-form
        ref="container"
        name="experiment-form"
        title="{{ $experiment->title() }}"
        :blueprint="{{ Js::from($blueprint) }}"
        :initial-values="{{ Js::from($values) }}"
        :initial-meta="{{ Js::from($meta) }}"
        submit-url="{{ cp_route('ab.experiments.update', $experiment->id()) }}"
    >
    </ui-publish-form>

@stop
