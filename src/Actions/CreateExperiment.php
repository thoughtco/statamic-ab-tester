<?php

namespace Thoughtco\StatamicABTester\Actions;

use Statamic\Actions\Action;
use Statamic\Contracts\Entries\Entry;
use Statamic\Statamic;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Facades\Goal;
use Thoughtco\StatamicCacheTracker\Facades\Tracker;

class CreateExperiment extends Action
{
    protected $component = 'ab-tester-experiment-setup';

    protected $runnable = false;

    public function run($items, $values)
    {
        $items->filter(fn ($item) => $item->absoluteUrl())
            ->each(fn ($item) => Tracker::remove($item->absoluteUrl()));

        return __('Cache cleared');
    }

    public function icon(): string
    {
        return 'labs-idea-experimental-flask';
    }

    public static function title()
    {
        return __('Setup A/B Experiment');
    }

    public function confirmationText()
    {
        return __('Are you sure you want to clear the static cache for the url: :url ?', ['url' => $this->items->first()->absoluteUrl()]);
    }

    public function visibleTo($item)
    {
        if (! auth()->user()->can('create a/b experiments')) {
            return false;
        }

        if ($this->context['view'] !== 'form') {
            return false;
        }

        if (! $item instanceof Entry) {
            return false;
        }

        return $item->collection()->route($item->locale());
    }

    public function toArray()
    {
        $item = $this->items->first();
        $blueprint = $item->blueprint();

        $existsQuery = Experiment::query()
            ->where('entry_id', $item->id())
            ->where('published', true)
            ->where(fn ($query) => $query->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('end_at')->orWhere('end_at', '<=', now()))
            ->first();

        return [
            ...parent::toArray(),
            'meta' => $blueprint->fields()->meta(),
            'ab_tester' => [
                'entry_id' => $item->id(),
                'exists' => $existsQuery ? Statamic::cpRoute('ab.experiments.show', ['experiment' => $existsQuery->id()]) : false,
                'fields' => $blueprint->fields()->toPublishArray(),
                'goals' => Goal::all()->map(fn ($goal) => ['label' => $goal->title(), 'value' => $goal->handle()])->all(),
                'route' => Statamic::cpRoute('ab.experiments.store'),
                'values' => $blueprint->fields()->values(),
            ],
        ];
    }
}
