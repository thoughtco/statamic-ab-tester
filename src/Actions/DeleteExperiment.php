<?php

namespace Thoughtco\StatamicABTester\Actions;

use Statamic\Actions\Action;
use Thoughtco\StatamicABTester\Contracts\Experiment;

class DeleteExperiment extends Action
{
    protected $dangerous = true;

    public function run($items, $values)
    {
        $items->each->delete();

        return trans_choice('Experiment deleted|Experiments deleted', $items->count());
    }

    public function icon(): string
    {
        return 'trash';
    }

    public static function title()
    {
        return __('Delete');
    }

    public function visibleTo($item)
    {
        if (! auth()->user()->can('create a/b goals')) {
            return false;
        }

        if (! $item instanceof Experiment) {
            return false;
        }

        return true;
    }

    public function buttonText()
    {
        return 'Delete|Delete :count items?';
    }

    public function confirmationText()
    {
        return 'Are you sure you want to delete this?|Are you sure you want to delete these :count items?';
    }

    public function bypassesDirtyWarning(): bool
    {
        return true;
    }
}
