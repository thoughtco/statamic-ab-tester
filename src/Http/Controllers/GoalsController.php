<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Facades\Goal;
use Thoughtco\StatamicABTester\Http\Resources\GoalsResource;

class GoalsController extends CpController
{
    use QueriesFilters;

    public function index()
    {
        return Inertia::render('abtester::Goals.Index', [
            'routes' => [
                'actions' => cp_route('ab.goals.actions'),
                'create' => cp_route('ab.goals.create'),
                'json' => cp_route('ab.goals.json'),
            ],
        ]);
    }

    public function json(Request $request)
    {
        $query = Goal::query();

        if ($searchQuery = $request->search ?? false) {
            $query->where('title', 'like', '%'.$searchQuery.'%');
        }

        if ($request->input('sort')) {
            $query->reorder($request->input('sort'), $request->input('order'));
        }

        $activeFilterBadges = $this->queryFilters($query, $request->filters, []);

        $results = $query->paginate($request->input('perPage', config('statamic.cp.pagination_size')));

        return (new GoalsResource($results))
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

        return Inertia::render('abtester::Goals.Create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'routes' => [
                'store' => cp_route('ab.goals.store'),
            ],
        ]);
    }

    public function show($goal)
    {
        abort_unless($goal = Goal::find($goal), 404);

        $experimentsWithThisGoal = Experiment::query()
            ->whereJsonContains('goals', $goal->id())
            ->get();

        $experimentResults = $experimentsWithThisGoal
            ->map(function ($experiment) {
                $success = $experiment->resultsQuery()->where('type', 'success')->count() ?? 0;
                $hits = $experiment->resultsQuery()->where('type', 'hit')->count() ?? 0;

                $status = 'draft';
                if ($experiment->published()) {
                    $status = $experiment->completedAt() ? 'completed' : 'active';
                }

                return [
                    'id' => $experiment->id(),
                    'label' => $experiment->title(),
                    'status' => [
                        'label' => ucfirst($status),
                        'color' => match ($status) {
                            'completed' => 'yellow',
                            'active' => 'green',
                            default => null,
                        },
                    ],
                    'hits' => $hits,
                    'success' => $success,
                    'failed' => $experiment->resultsQuery()->where('type', 'failures')->count() ?? 0,
                    'rate' => 100 * round($success / ($hits ?: 1), 4),
                    'show_url' => cp_route('ab.experiments.show', $experiment->id()),
                ];
            })
            ->sortBy('label')
            ->values()
            ->filter();

        return Inertia::render('abtester::Goals.Show', [
            'goal' => $goal,
            'hasResults' => $experimentResults->isNotEmpty(),
            'results' => [
                'experiments' => $experimentResults->all(),
                'experimentsTotal' => [
                    'hits' => $experimentResults->sum('hits'),
                    'success' => $experimentResults->sum('success'),
                    'failed' => $experimentResults->sum('failed'),
                    'rate' => 100 * round($experimentResults->sum('success') / ($experimentResults->sum('hits') ?: 1), 4),
                ],
            ],
            'routes' => [
                'edit' => cp_route('ab.goals.edit', $goal->id()),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $fields = Goal::blueprint()->fields()->addValues($request->all());

        $fields->validate();

        if (Goal::find($request->input('handle'))) {
            throw ValidationException::withMessages([
                'handle' => __('A goal with this handle already exists.'),
            ]);
        }

        $values = $fields->process()->values();

        $goal = tap(
            Goal::make()
                ->title($values->get('title'))
                ->handle($values->get('handle'))
                ->data($values->except(['title', 'handle'])->all())
        )
            ->save();

        session()->flash('success', __('Goal Created'));

        return ['redirect' => cp_route('ab.goals.show', $goal->id())];
    }

    public function edit($goal)
    {
        abort_unless($goal = Goal::find($goal), 404);

        $blueprint = Goal::blueprint();

        $fields = $blueprint->fields()->addValues($goal->toArray())->preProcess();

        return Inertia::render('abtester::Goals.Edit', [
            'goal' => $goal,
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'routes' => [
                'submit' => cp_route('ab.goals.update', $goal->id()),
            ],
        ]);
    }

    public function update(Request $request, $goal)
    {
        abort_unless($goal = Goal::find($goal), 404);

        $fields = Goal::blueprint()->fields()->addValues($request->all());

        $fields->validate();

        $values = $fields->process()->values();

        $goal->title($values->get('title'))
            ->handle($values->get('handle'))
            ->data($values->except(['title', 'handle'])->all())
            ->save();

        $this->success(__('Goal Saved'));
    }

    public function destroy($goal)
    {
        abort_unless($goal = Goal::find($goal), 404);

        $goal->delete();
    }
}
