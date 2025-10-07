@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Goals'), $goal->title()))

@section('content')

    <ui-publish-form
        ref="container"
        name="goal-form"
        title="{{ $goal->title() }}"
        :blueprint="{{ Js::from($blueprint) }}"
        :initial-values="{{ Js::from($values) }}"
        :initial-meta="{{ Js::from($meta) }}"
        submit-url="{{ cp_route('ab.goals.update', $goal->id()) }}"
    >
    </ui-publish-form>

@stop
