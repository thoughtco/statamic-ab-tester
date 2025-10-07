@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Create Goal')))

@section('content')

    <ui-publish-form
        ref="container"
        name="goal-form"
        title="{{ __('Create a goal') }}"
        :blueprint="{{ Js::from($blueprint) }}"
        :initial-values="{{ Js::from($values) }}"
        :initial-meta="{{ Js::from($meta) }}"
        submit-url="{{ cp_route('ab.goals.store') }}"
        submit-method="POST"
    >
    </ui-publish-form>

@stop
