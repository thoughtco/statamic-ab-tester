<?php

namespace Thoughtco\StatamicABTester\Experiment;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Statamic\Data\ContainsData;
use Statamic\Data\Publishable;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Thoughtco\StatamicABTester\Contracts\Experiment as ExperimentContract;
use Thoughtco\StatamicABTester\Events;
use Thoughtco\StatamicABTester\Facades\Experiment as ExperimentFacade;
use Thoughtco\StatamicABTester\Models\AbTestResult;

abstract class Experiment implements Arrayable, ExperimentContract
{
    use ContainsData, FluentlyGetsAndSets, Publishable;

    protected $afterSaveCallbacks = [];

    protected $endAt;

    protected $goals = [];

    protected $id;

    protected $startAt;

    protected $title;

    protected $type;

    protected $withEvents = true;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function endAt($endAt = null)
    {
        return $this->fluentlyGetOrSet('endAt')
            ->getter(function ($endAt) {
                if (! $endAt) {
                    return;
                }

                return $endAt instanceof Carbon ? $endAt : Carbon::createFromTimestamp($endAt);
            })
            ->setter(function ($endAt) {
                return $endAt instanceof Carbon ? $endAt : ($endAt ? Carbon::parse($endAt) : null);
            })
            ->args(func_get_args());
    }

    public function goals($goals = null)
    {
        return $this->fluentlyGetOrSet('goals')
            ->getter(function ($goals) {
                return collect($goals ?? []);
            })
            ->args(func_get_args());
    }

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->args(func_get_args());
    }

    public function resultsQuery()
    {
        return AbTestResult::query()->where('experiment_id', $this->id());
    }

    public function startAt($startAt = null)
    {
        return $this->fluentlyGetOrSet('startAt')
            ->getter(function ($startAt) {
                if (! $startAt) {
                    return;
                }

                return $startAt instanceof Carbon ? $startAt : Carbon::createFromTimestamp($startAt);
            })
            ->setter(function ($startAt) {
                return $startAt instanceof Carbon ? $startAt : ($startAt ? Carbon::parse($startAt) : null);
            })
            ->args(func_get_args());
    }

    public function title($title = null)
    {
        return $this->fluentlyGetOrSet('title')->args(func_get_args());
    }

    public function type($type = null)
    {
        return $this->fluentlyGetOrSet('type')->args(func_get_args());
    }

    private function createResultModel($type, $variantId, $goalId, $data)
    {
        AbTestResult::create([
            'data' => $data,
            'experiment_id' => $this->id(),
            'variation' => $variantId,
            'goal_id' => $goalId,
            'ip_address' => request()->ip(),
            'type' => $type,
            'user_id' => auth()->user()?->id(),
        ]);
    }

    public function recordHit($variantId, $data = [])
    {
        $this->createResultModel('hit', $variantId, null, $data);

        return $this;
    }

    public function recordFailure($variantId, $goalId, $data = [])
    {
        $this->createResultModel('failure', $variantId, $goalId, $data);

        return $this;
    }

    public function recordSuccess($variantId, $goalId, $data = [])
    {
        $this->createResultModel('success', $variantId, $goalId, $data);

        return $this;
    }

    public function delete()
    {
        if (Events\ExperimentDeleting::dispatch($this) === false) {
            return false;
        }

        ExperimentFacade::delete($this);

        Events\ExperimentDeleted::dispatch($this);

        return true;
    }

    public function afterSave($callback)
    {
        $this->afterSaveCallbacks[] = $callback;

        return $this;
    }

    public function saveQuietly()
    {
        $this->withEvents = false;

        return $this->save();
    }

    public function save()
    {
        $isNew = is_null(ExperimentFacade::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew && Events\ExperimentCreating::dispatch($this) === false) {
                return false;
            }

            if (Events\ExperimentSaving::dispatch($this) === false) {
                return false;
            }
        }

        ExperimentFacade::save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                Events\ExperimentCreated::dispatch($this);
            }

            Events\ExperimentSaved::dispatch($this);
        }

        return true;
    }

    public function toArray()
    {
        return array_merge($this->data->all(), [
            'id' => $this->id(),
            'title' => $this->title(),
            'type' => $this->type(),
            'goals' => $this->goals,
            'start_at' => $this->startAt,
            'end_at' => $this->endAt,
            'published' => $this->published,
        ]);
    }

    public function chooseVariation()
    {
        if ($this->type() == 'item') {
            if (! $variant = session()->get('statamic.ab.'.$this->id())) {
                $variant = rand(1, 2);
            }

            return $variant;
        }

        return null;
    }
}
