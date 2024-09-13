<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\CP\Column;
use Statamic\Http\Controllers\CP\CpController;
use Thoughtco\StatamicABTester\Facades\Experiment;

class ExperimentsController extends CpController
{
    public function index()
    {
        return view('ab::experiments.index', [
            'experiments' => Experiment::all()->map(function ($experiment) {
                return $experiment->toArray() + [
                    'url' => cp_route('ab.experiments.show', $experiment->handle()),
                    'edit_url' => cp_route('ab.experiments.edit', $experiment->handle()),
                    'delete_url' => cp_route('ab.experiments.delete', $experiment->handle()),
                ];
            }),
            'columns' => [
                Column::make('title')->label(__('Title')),
                Column::make('handle')->label(__('Handle')),
            ],
        ]);
    }

    public function show($experiment)
    {
        return view('ab::experiments.show', [
            'experiment' => Experiment::find($experiment),
            'columns' => [
                Column::make('label')->label(__('Variant')),
                Column::make('hits')->label(__('Hits')),
                Column::make('successful')->label(__('Successful')),
                Column::make('failed')->label(__('Failed')),
            ],
        ]);
    }

    public function create()
    {
        $blueprint = Experiment::blueprint();
        $fields = $blueprint->fields()->addValues([])->preProcess();

        return view('ab::experiments.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ]);
    }

    public function store(Request $request)
    {
        $fields = Experiment::blueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();

        if (Experiment::find($values->get('handle'))) {
            throw ValidationException::withMessages(['handle' => __('Experiment with this handle already exists.')]);

            return;
        }

        $experiment = tap(Experiment::make()
            ->title($values->get('title'))
            ->handle($values->get('handle'))
            ->variants($values->get('variants'))
            ->type($values->get('type')))
            ->save();

        session()->flash('success', __('Experiment Created'));

        return ['redirect' => cp_route('ab.experiments.show', $experiment->handle())];
    }

    public function edit($experiment)
    {
        abort_unless($experiment = Experiment::find($experiment), 404);

        $blueprint = Experiment::blueprint();

        $fields = $blueprint->fields()->addValues($experiment->toArray())->preProcess();

        return view('ab::experiments.edit', [
            'experiment' => $experiment,
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ]);
    }

    public function update(Request $request, $experiment)
    {
        abort_unless($experiment = Experiment::find($experiment), 404);

        $fields = Experiment::blueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();

        $experiment->title($values->get('title'))
            ->handle($values->get('handle'))
            ->variants($values->get('variants'))
            ->type($values->get('type'))
            ->save();

        $this->success(__('Saved'));
    }

    public function destroy($experiment)
    {
        abort_unless($experiment = Experiment::find($experiment), 404);

        $experiment->delete();
    }

    public function results($experiment)
    {
        return response(['results' => Experiment::find($experiment)->results()]);
    }
}
