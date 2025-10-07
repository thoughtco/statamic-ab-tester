<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Facades\Goal;
use Thoughtco\StatamicABTester\Http\Resources\ExperimentsResource;

class GoalsController extends CpController
{
    use QueriesFilters;

    public function index()
    {
        return view('ab::goals.index', [
            'experiments' => Experiment::all()->map(function ($experiment) {
                return $experiment->toArray() + [
                    'url' => cp_route('ab.experiments.show', $experiment->id()),
                    'edit_url' => cp_route('ab.experiments.edit', $experiment->id()),
                    'delete_url' => cp_route('ab.experiments.delete', $experiment->id()),
                ];
            }),
            'columns' => (new Columns([
                Column::make('title')->label(__('Title')),
                Column::make('handle')->label(__('Handle')),
            ]))
                ->setPreferred('ab.goals.columns')
                ->rejectUnlisted()
                ->values(),
        ]);
    }

    public function json(Request $request)
    {
        $query = Goal::query();

        if ($searchQuery = $request->search ?? false) {
            $query->where('title', 'like', '%'.$searchQuery.'%');
        }

        $activeFilterBadges = $this->queryFilters($query, $request->filters, []);

        $results = $query->paginate($request->input('perPage', config('statamic.cp.pagination_size')));

        return (new ExperimentsResource($results))
            ->setColumnPreferenceKey('ab.goals.columns')
            ->additional([
                'meta' => [
                    'activeFilterBadges' => $activeFilterBadges,
                ],
            ]);
    }

    public function create()
    {
        $blueprint = Goal::blueprint();

        $fields = $blueprint->fields()->preProcess();

        return view('ab::goals.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
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

    public function store(Request $request)
    {
        $request->validate([
            'entry_id' => ['required'],
            'title' => ['required'],
            'fields' => ['required', 'array'],
            'goals' => ['required', 'array'],
            'values' => ['required', 'array'],
        ]);

        $fields = Experiment::blueprint()->fields()->only($request->input('fields', []))->addValues($request->input('values', []));

        try {
            $fields->validate();
        } catch (ValidationException $e) {
            throw ValidationException::withMessages(collect($e->errors())->mapWithKeys(fn ($errors, $key) => ['values.'.$key => $errors])->all());
        }

        $values = $fields->process()->values();

        $experiment = tap(
            Experiment::make()
                ->title($values->get('title'))
                ->goals($values->get('goals'))
                ->type('entry') // for now we only have one experiment type, but that will change
                ->data([
                    'entry_id' => $values->get('entry_id'),
                    'fields' => $values->get('fields'),
                    'values' => $values->get('values'),
                ])
        )
            ->save();

        session()->flash('success', __('Experiment Created'));

        return ['redirect' => cp_route('ab.experiments.show', $experiment->id())];
    }

    public function edit($experiment)
    {
        abort_unless($experiment = Experiment::find($experiment), 404);

        $blueprint = Experiment::blueprint();

        $fields = $blueprint->fields()->addValues($experiment->toArray())->preProcess();

        return view('ab::goals.edit', [
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
            ->goals($values->get('goals'))
            ->type($values->get('type'))
            ->save();

        $this->success(__('Experiment Saved'));
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
