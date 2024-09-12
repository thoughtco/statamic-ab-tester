<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\CP\Column;
use Statamic\Http\Controllers\Controller;
use Thoughtco\StatamicABTester\Facades\Experiment;

class ExperimentsController extends Controller
{
    public function index()
    {
        return view('ab::experiments.index', [
            'experiments' => Experiment::all()->map(function ($experiment) {
                return $experiment->toArray() + [
                    'url' => cp_route('ab.experiments.show', $experiment->handle()),
                    'edit_url' => cp_route('ab.experiments.edit', $experiment->handle()),
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

        $experiment = Experiment::create($fields->process()->values());

        return ['redirect' => cp_route('ab.experiments.show', $experiment->handle())];
    }

    public function edit($experiment)
    {
        abort_unless($experiment = Experiment::find($experiment), 404);

        $blueprint = Experiment::blueprint();

        $fields = $blueprint->fields()->addValues($experiment->fields())->preProcess();

        return view('ab::experiments.edit', [
            'experiment' => $experiment,
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
        ]);
    }

    public function update(Request $request, $experiment)
    {
        $blueprint = Experiment::blueprint();
        $experiment = Experiment::find($experiment);

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate();

        if ($redirect = $experiment->update($fields->process()->values())) {
            return ['redirect' => $redirect];
        }

        return response()->noContent();
    }

    public function results($experiment)
    {
        return response(['results' => Experiment::find($experiment)->results()]);
    }
}
