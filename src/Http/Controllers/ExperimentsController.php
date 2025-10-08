<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Statamic\Facades\Data;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Http\Resources\ExperimentsResource;

class ExperimentsController extends CpController
{
    use QueriesFilters;

    public function index()
    {
        return view('ab::experiments.index', [
            'experimentsIsEmpty' => Experiment::query()->count() <= 0,
            'columns' => (new Columns([
                Column::make('title')->label(__('Title')),
                Column::make('id')->label(__('ID')),
            ]))
                ->setPreferred('ab.experiments.columns')
                ->rejectUnlisted()
                ->values(),
        ]);
    }

    public function json(Request $request)
    {
        $query = Experiment::query();

        if ($searchQuery = $request->search ?? false) {
            $query->where('title', 'like', '%'.$searchQuery.'%');
        }

        if ($request->input('sort')) {
            $query->reorder($request->input('sort'), $request->input('order'));
        }

        $activeFilterBadges = $this->queryFilters($query, $request->filters, []);

        $results = $query->paginate($request->input('perPage', config('statamic.cp.pagination_size')));

        return (new ExperimentsResource($results))
            ->setColumnPreferenceKey('ab.experiments.columns')
            ->additional([
                'meta' => [
                    'activeFilterBadges' => $activeFilterBadges,
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

    public function store(Request $request)
    {
        $request->validate([
            'entry_id' => ['required'],
            'title' => ['required'],
            'experiment_fields' => ['required', 'array'],
            'goals' => ['required', 'array'],
            'published' => ['nullable', 'boolean'],
        ]);

        $fields = Data::find($request->input('entry_id'))->blueprint()->fields()
            ->only($request->input('experiment_fields.fields', []))
            ->addValues($request->input('experiment_fields.values', []));

        try {
            $fields->validate();
        } catch (ValidationException $e) {
            throw ValidationException::withMessages(collect($e->errors())->mapWithKeys(fn ($errors, $key) => ['experiment_fields.values.'.$key => $errors])->all());
        }

        $experiment = tap(
            Experiment::make()
                ->title($request->input('title'))
                ->goals($request->input('goals'))
                ->type('entry') // for now we only have one experiment type, but that will change
                ->data([
                    'entry_id' => $request->input('entry_id'),
                    'experiment_fields' => $request->input('experiment_fields'),
                ])
                ->published($request->input('published', true))
        )
            ->save();

        session()->flash('success', __('Experiment Created'));

        return ['redirect' => cp_route('ab.experiments.show', $experiment->id())];
    }

    public function edit($experiment)
    {
        abort_unless($experiment = Experiment::find($experiment), 404);

        $blueprint = Experiment::blueprint();

        $fields = $blueprint->fields()->setParent($experiment)->addValues($experiment->toArray())->preProcess();

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

        $request = $request->merge([
            'entry_id' => $experiment->get('entry_id'),
        ]);

        $fields = Experiment::blueprint()->fields()->setParent($experiment)->addValues($request->all());

        $fields->validate();

        $fields = Data::find($experiment->get('entry_id'))
            ->blueprint()->fields()
            ->only($request->input('experiment_fields.fields', []))
            ->addValues($request->input('experiment_fields.values', []));

        try {
            $fields->validate();
        } catch (ValidationException $e) {
            throw ValidationException::withMessages(collect($e->errors())->mapWithKeys(fn ($errors, $key) => ['experiment_fields.values.'.$key => $errors])->all());
        }

        $experiment->title($request->input('title'))
            ->goals($request->input('goals'))
            ->type($request->input('type'))
            ->type('entry') // for now we only have one experiment type, but that will change
            ->merge([
                'experiment_fields' => $request->input('experiment_fields'),
            ])
            ->published($request->input('published', false))
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
