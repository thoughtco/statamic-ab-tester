<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Facades\Data;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Http\Resources\ExperimentsResource;

class ExperimentsController extends CpController
{
    use QueriesFilters;

    public function index()
    {
        return Inertia::render('AB/Experiments/Index', [
            'experimentsIsEmpty' => Experiment::query()->count() <= 0,
            'routes' => [
                'actions' => cp_route('ab.experiments.actions'),
                'create' => cp_route('ab.goals.create'),
                'json' => cp_route('ab.experiments.json'),
            ],
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
        abort_unless($experiment = Experiment::find($experiment), 404);

        $variantResults = $experiment->resultsQuery()
            ->select('variation', DB::raw('count(*) as hits'))
            ->distinct()
            ->get()
            ->map(function ($row) use ($experiment) {
                $success = $experiment->resultsQuery()->where('variation', $row['variation'])->where('type', 'success')->count() ?? 0;

                return [
                    'label' => $row['variation'],
                    'hits' => $row['hits'],
                    'success' => $success,
                    'failed' => $experiment->resultsQuery()->where('variation', $row['variation'])->where('type', 'failures')->count() ?? 0,
                    'rate' => 100 * round($success / ($row['hits'] ?? 1), 4),
                ];
            })
            ->all();

        $userResults = $experiment->resultsQuery()
            ->select('user_id', DB::raw('count(*) as hits'))
            ->distinct()
            ->orderBy('hits')
            ->limit(25)
            ->get()
            ->map(function ($row) use ($experiment) {
                if (! $user = User::find($row['user_id'])) {
                    return null;
                }

                $success = $experiment->resultsQuery()->where('user_id', $row['user_id'])->where('type', 'success')->count() ?? 0;

                return [
                    'label' => $user->name(),
                    'hits' => $row['hits'],
                    'success' => $success,
                    'rate' => 100 * round($success / ($row['hits'] ?? 1), 4),
                ];
            })
            ->filter();

        $ipResults = $experiment->resultsQuery()
            ->select('ip_address', DB::raw('count(*) as hits'))
            ->distinct()
            ->orderBy('hits')
            ->limit(25)
            ->get()
            ->map(function ($row) use ($experiment) {
                $success = $experiment->resultsQuery()->where('ip_address', $row['ip_address'])->where('type', 'success')->count() ?? 0;

                return [
                    'label' => $row['ip_address'],
                    'hits' => $row['hits'],
                    'success' => $success,
                    'rate' => 100 * round($success / ($row['hits'] ?? 1), 4),
                ];
            })
            ->filter();

        return Inertia::render('AB/Experiments/Show', [
            'experiment' => $experiment,
            'hasResults' => count($variantResults) > 0,
            'results' => [
                'ip' => $ipResults,
                'user' => $userResults,
                'variant' => $variantResults,
            ],
            'routes' => [
                'edit' => cp_route('ab.experiments.edit', $experiment->id()),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => ['required'],
            'title' => ['required'],
            'experiment_fields' => ['required', 'array'],
            'goals' => ['required', 'array'],
            'published' => ['nullable', 'boolean'],
        ]);

        $fields = Data::find($request->input('item_id'))->blueprint()->fields()
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
                ->type('item') // for now we only have one experiment type, but that will change
                ->data([
                    'item_id' => $request->input('item_id'),
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

        return Inertia::render('AB/Experiments/Edit', [
            'experiment' => $experiment,
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'routes' => [
                'submit' => cp_route('ab.experiments.update', $experiment->id()),
            ],
        ]);
    }

    public function update(Request $request, $experiment)
    {
        abort_unless($experiment = Experiment::find($experiment), 404);

        $request = $request->merge([
            'item_id' => $experiment->get('item_id'),
        ]);

        $fields = Experiment::blueprint()->fields()->setParent($experiment)->addValues($request->all());

        $fields->validate();

        $fields = Data::find($experiment->get('item_id'))
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
            ->type('item') // for now we only have one experiment type, but that will change
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
}
