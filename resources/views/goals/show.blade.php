@extends('statamic::layout')
@section('title', Statamic::crumb(__('A/B'), __('Goals'), $goal->title()))

@section('content')

    <ui-header title="{{ $goal->title() }}" icon="favorite-trophy">
        <ui-button href="{{ cp_route('ab.goals.edit', $goal->id()) }}" class="btn-primary">{{ __('Edit') }}</ui-button>
    </ui-header>

    <p>Link to experiments using this Goal</p>
@stop
