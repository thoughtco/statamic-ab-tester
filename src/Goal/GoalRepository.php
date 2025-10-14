<?php

namespace Thoughtco\StatamicABTester\Goal;

use Statamic\Data\DataCollection;
use Statamic\Facades\Blueprint;
use Thoughtco\StatamicABTester\Contracts\Goal as GoalContract;
use Thoughtco\StatamicABTester\Contracts\GoalRepository as RepositoryContract;
use Thoughtco\StatamicABTester\Facades\Experiment;

abstract class GoalRepository implements RepositoryContract
{
    public function all(): DataCollection
    {
        return $this->query()->get();
    }

    public function find($id): ?GoalContract
    {
        return $this->query()->where('id', $id)->first();
    }

    public function make(): GoalContract
    {
        return app(GoalContract::class);
    }

    public static function bindings()
    {
        return [];
    }

    public function blueprint()
    {
        return Blueprint::makeFromFields([
            'title' => [
                'type' => 'text',
                'validate' => 'required',
            ],
            'handle' => [
                'type' => 'slug',
                'validate' => 'required',
            ],
        ]);
    }

    public function completed($handle, $data = [])
    {
        if (! $goal = $this->query()->where('handle', $handle)->first()) {
            return false;
        }

        if (! $experimentsWithThisGoal = $this->getExperimentsForGoal($handle)) {
            return false;
        }

        $experimentsWithThisGoal->each(function ($experiment) use ($data, $goal) {
            if (! $variantId = session()->has('statamic.ab.'.$experiment->id())) {
                return;
            }

            $experiment->recordSuccess($variantId, $goal->id(), $data);
        });

    }

    public function failed($handle, $data = [])
    {
        if (! $goal = $this->query()->where('handle', $handle)->first()) {
            return false;
        }

        if (! $experimentsWithThisGoal = $this->getExperimentsForGoal($handle)) {
            return false;
        }

        $experimentsWithThisGoal->each(function ($experiment) use ($data, $goal) {
            if (! $variantId = session()->has('statamic.ab.'.$experiment->id())) {
                return;
            }

            $experiment->recordFailure($variantId, $goal->id(), $data);
        });

    }

    private function getExperimentsForGoal($handle)
    {
        if (! $goal = $this->query()->where('handle', $handle)->first()) {
            return false;
        }

        $experimentsWithThisGoal = Experiment::query()
            ->where('published', true)
            ->whereNull('completed_at')
            ->where(fn ($query) => $query->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('end_at')->orWhere('end_at', '>=', now()))
            ->get()
            ->filter(fn ($experiment) => in_array($goal->id(), $experiment->goals()->all()));

        if ($experimentsWithThisGoal->isEmpty()) {
            return false;
        }

        return $experimentsWithThisGoal;
    }
}
