<?php

namespace Thoughtco\StatamicABTester\Goal;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Data\ContainsData;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Thoughtco\StatamicABTester\Contracts\Goal as GoalContract;
use Thoughtco\StatamicABTester\Events;
use Thoughtco\StatamicABTester\Facades\Goal as GoalFacade;
use Thoughtco\StatamicABTester\Models\AbTestResult;

abstract class Goal implements Arrayable, GoalContract
{
    use ContainsData, FluentlyGetsAndSets;

    protected $afterSaveCallbacks = [];

    protected $handle;

    protected $id;

    protected $title;

    protected $withEvents = true;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->args(func_get_args());
    }

    public function resultsQuery()
    {
        return AbTestResult::query()->where('goal_id', $this->id());
    }

    public function title($title = null)
    {
        return $this->fluentlyGetOrSet('title')->args(func_get_args());
    }

    public function delete()
    {
        if (Events\GoalDeleting::dispatch($this) === false) {
            return false;
        }

        GoalFacade::delete($this);

        Events\GoalDeleted::dispatch($this);

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
        $isNew = is_null(GoalFacade::find($this->handle()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if ($isNew && Events\GoalCreating::dispatch($this) === false) {
                return false;
            }

            if (Events\GoalSaving::dispatch($this) === false) {
                return false;
            }
        }

        GoalFacade::save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                Events\GoalCreated::dispatch($this);
            }

            Events\GoalSaved::dispatch($this);
        }

        return true;
    }

    public function toArray()
    {
        return array_merge($this->data->all(), [
            'handle' => $this->handle(),
            'title' => $this->title(),
        ]);
    }
}
