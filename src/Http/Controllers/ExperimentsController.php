<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Exceptions\UnauthorizedHttpException;
use Statamic\Facades\Data;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Support\Arr;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Http\Resources\ExperimentsResource;

class ExperimentsController extends CpController
{
    use QueriesFilters;

    public function index()
    {
        return Inertia::render('abtester::Experiments.Index', [
            'experimentsIsEmpty' => Experiment::query()->count() <= 0,
            'routes' => [
                'actions' => cp_route('ab.experiments.actions'),
                'create' => cp_route('ab.experiments.create'),
                'goal_create' => cp_route('ab.goals.create'),
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

    public function create()
    {
        $blueprint = Experiment::blueprint();

        $fields = $blueprint->fields()->preProcess();

        return Inertia::render('abtester::Experiments.Create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'routes' => [
                'store' => cp_route('ab.experiments.store'),
            ],
        ]);
    }

    public function show($experiment)
    {
        throw_unless($experiment = Experiment::find($experiment), NotFoundHttpException::class);

        $variantResults = $experiment->resultsQuery()
            ->select('variation', DB::raw('count(*) as hits'))
            ->groupBy('variation')
            ->get()
            ->map(function ($row) use ($experiment) {
                $success = $experiment->resultsQuery()->where('variation', $row['variation'])->where('type', 'success')->count() ?? 0;

                return [
                    'id' => $row['variation'], // just in case we label them
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
            ->groupBy('user_id')
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
            ->groupBy('ip_address')
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

        return Inertia::render('abtester::Experiments.Show', [
            'experiment' => $experiment,
            'hasResults' => count($variantResults) > 0,
            'results' => [
                'ip' => $ipResults,
                'user' => $userResults,
                'variant' => $variantResults,
            ],
            'routes' => [
                'edit' => cp_route('ab.experiments.edit', $experiment->id()),
                'complete' => cp_route('ab.experiments.complete', $experiment->id()),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required'],
            'item_id' => ['required_if:type,item'],
            'experiment_fields' => ['required_if:type,item', 'array'],
            'manual_fields' => ['required_if:type,manual', 'array'],
            'goals' => ['required', 'array'],
            'published' => ['nullable', 'boolean'],
            'type' => ['required', 'in:item,manual'],
        ]);

        if ($request->input('type') === 'item') {
            $fields = Data::find($request->input('item_id'))->blueprint()->fields()
                ->only($request->input('experiment_fields.fields', []))
                ->addValues($request->input('experiment_fields.values', []));

            try {
                $fields->validate();
            } catch (ValidationException $e) {
                throw ValidationException::withMessages(collect($e->errors())->mapWithKeys(fn ($errors, $key) => ['experiment_fields.values.'.$key => $errors])->all());
            }
        }

        $experiment = tap(
            Experiment::make()
                ->title($request->input('title'))
                ->goals($request->input('goals'))
                ->type($request->input('type'))
                ->startAt($request->input('start_at'))
                ->endAt($request->input('end_at'))
                ->data(Arr::removeNullValues([
                    'item_id' => $request->input('item_id'),
                    'experiment_fields' => $request->input('experiment_fields'),
                    'manual_fields' => $request->input('manual_fields'),
                ]))
                ->published($request->input('published', true))
        )
            ->save();

        session()->flash('success', __('Experiment Created'));

        return ['redirect' => cp_route('ab.experiments.show', $experiment->id())];
    }

    public function edit($experiment)
    {
        throw_unless($experiment = Experiment::find($experiment), NotFoundHttpException::class);

        throw_if($experiment->completedAt(), UnauthorizedHttpException::class);

        $blueprint = Experiment::blueprint(editing: true);

        $fields = $blueprint->fields()->setParent($experiment);

        $fields = $fields->addValues($experiment->toArray())->preProcess();

        return Inertia::render('abtester::Experiments.Edit', [
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

        abort_if($experiment->completedAt(), 403);

        $request = $request->merge([
            'item_id' => $experiment->get('item_id'),
        ]);

        $fields = Experiment::blueprint()->fields()->setParent($experiment)->addValues($request->all());

        $fields->validate();

        if ($request->input('type') === 'item') {
            $fields = Data::find($experiment->get('item_id'))
                ->blueprint()->fields()
                ->only($request->input('experiment_fields.fields', []))
                ->addValues($request->input('experiment_fields.values', []));

            try {
                $fields->validate();
            } catch (ValidationException $e) {
                throw ValidationException::withMessages(collect($e->errors())->mapWithKeys(fn ($errors, $key) => ['experiment_fields.values.'.$key => $errors])->all());
            }
        }

        $experiment->title($request->input('title'))
            ->goals($request->input('goals'))
            ->type($request->input('type'))
            ->startAt($request->input('start_at'))
            ->endAt($request->input('end_at'))
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

    public function complete(Request $request, $experiment)
    {
        abort_unless($experiment = Experiment::find($experiment), 404);

        abort_if($experiment->completedAt(), 403);

        $request->validate([
            'variant' => ['required'],
        ]);

        $variant = $request->input('variant');

        $experiment->completedAt(now())
            ->merge([
                'winner' => $variant,
            ])
            ->save();

        // if an item experiment, and the winner is the new field version
        // we need to apply the values to the original item
        if ($experiment->type() == 'item' && $variant == 2) {
            if ($item = Data::find($experiment->get('item_id'))) {
                $item->merge($experiment->get('experiment_fields.values', []))->save();
            }
        }

        return [
            'experiment' => $experiment->toArray(),
        ];
    }
}
