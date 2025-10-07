<?php

namespace Thoughtco\StatamicABTester\Actions;

use Statamic\Actions\Action;
use Statamic\Contracts\Entries\Entry;
use Statamic\Statamic;
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

        return [
            ...parent::toArray(),
            'meta' => $blueprint->fields()->meta(),
            'ab_tester' => [
                'entry_id' => $item->id(),
                'exists' => false, // Statamic::cpRoute('ab.experiments.index'),
                'fields' => $blueprint->fields()->toPublishArray(),
                'goals' => Goal::all()->map(fn ($goal) => ['value' => $goal->title(), 'key' => $goal->id()])->all(),
                'route' => Statamic::cpRoute('ab.experiments.store'),
                'values' => $blueprint->fields()->values(),
            ],
        ];
    }
}
