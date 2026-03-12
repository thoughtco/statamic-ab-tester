<?php

namespace Thoughtco\StatamicABTester\Actions;

use Statamic\Actions\Action;
use Statamic\Support\Arr;
use Thoughtco\StatamicABTester\Contracts\Experiment;
use Thoughtco\StatamicABTester\Facades\Experiment as ExperimentFacade;

class DuplicateExperiment extends Action
{
    protected $lastCreated;

    public function run($items, $values)
    {
        $items->each(function ($experiment) {
            $copy = ExperimentFacade::make()
                ->title(__('Copy of :title', ['title' => $experiment->title()]))
                ->type($experiment->type())
                ->goals($experiment->goals())
                ->data(Arr::removeNullValues($experiment->data()->all()))
                ->published(false);

            $copy->save();

            $this->lastCreated = $copy;
        });

        return trans_choice('Experiment duplicated|Experiments duplicated', $items->count());
    }

    public function redirect($items, $values)
    {
        if ($items->count() === 1) {
            return cp_route('ab.experiments.edit', $this->lastCreated->id());
        }

        return false;
    }

    public function icon(): string
    {
        return 'copy';
    }

    public static function title()
    {
        return __('Duplicate');
    }

    public function visibleTo($item)
    {
        return $item instanceof Experiment;
    }

    public function authorize($user, $item)
    {
        return $user->can('create a/b experiments');
    }
}
