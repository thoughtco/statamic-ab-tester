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
use Thoughtco\StatamicABTester\Http\Resources\GoalsResource;

class GoalsController extends CpController
{
    use QueriesFilters;

    public function index()
    {
        return view('ab::goals.index', [
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

        return ['redirect' => cp_route('ab.goal.show', $goal->handle())];
    }

    public function edit($goal)
    {
        abort_unless($goal = Experiment::find($goal), 404);

        $blueprint = Goal::blueprint();

        $fields = $blueprint->fields()->addValues($goal->toArray())->preProcess();

        return view('ab::goals.edit', [
            'goal' => $goal,
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
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
