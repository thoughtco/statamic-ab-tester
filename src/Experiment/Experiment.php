<?php

namespace Thoughtco\StatamicABTester\Experiment;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Statamic\Data\ContainsData;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Thoughtco\StatamicABTester\Contracts\Experiment as ExperimentContract;
use Thoughtco\StatamicABTester\Events;
use Thoughtco\StatamicABTester\Facades\Experiment as ExperimentFacade;

abstract class Experiment implements Arrayable, ExperimentContract
{
    use ContainsData, FluentlyGetsAndSets;

    protected $afterSaveCallbacks = [];

    protected $endAt;

    protected $handle;

    protected $results = [];

    protected $startAt;

    protected $title;

    protected $type;

    protected $variants = [];

    protected $withEvents = true;

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

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function results($results = null)
    {
        if ($results === null) {
            return collect($this->results)->map(function ($result, $variantId) {
                $result['label'] = Arr::get($this->variants()->firstWhere('id', $variantId), 'label') ?: $variantId;

                return $result;
            });
        }

        $this->results = collect($this->variants())->mapWithKeys(function ($variant) use ($results) {
            return [$variant['id'] => Arr::get($results, $variant['id'], ['hits' => 0, 'successful' => 0, 'failed' => 0])];
        })->toArray();

        return $this;
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

    public function variants($variants = null)
    {
        return $this->fluentlyGetOrSet('variants')
            ->getter(function ($variants) {
                return collect($variants ?? []);
            })
            ->args(func_get_args());
    }

    public function recordHit($variant)
    {
        if (! $this->results) {
            $this->results([]);
        }

        if (! Arr::has($this->results, $variant)) {
            return $this;
        }

        $this->results[$variant]['hits']++;

        $this->save();

        return $this;
    }

    public function recordFailure($variant)
    {
        if (! $this->results) {
            $this->results([]);
        }

        if (! Arr::has($this->results, $variant)) {
            return $this;
        }

        $this->results[$variant]['failed']++;

        $this->save();

        return $this;
    }

    public function recordSuccess($variant)
    {
        if (! $this->results) {
            $this->results([]);
        }

        if (! Arr::has($this->results, $variant)) {
            return $this;
        }

        $this->results[$variant]['successful']++;

        $this->save();

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
        $isNew = is_null(ExperimentFacade::find($this->handle()));

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

    public function saveResults()
    {
        File::put($this->resultsPath(), YAML::dump($this->results));

        return $this;
    }

    protected function getResultsFor($variantId)
    {
        if (! $variant = $this->variants()->firstWhere('id', $variantId)) {
            throw new \Exception(__('Variant not found on this experiment'));
        }

        return array_merge([
            'label' => Arr::get($variant, 'label', $variantId),
        ], Arr::get($this->results, $variantId, ['hits' => 0, 'successful' => 0, 'failed' => 0]));
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle,
            'title' => $this->title,
            'variants' => $this->variants,
            'type' => $this->type,
            'results' => $this->results,
        ];
    }
}
