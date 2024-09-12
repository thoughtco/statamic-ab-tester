<?php

namespace Thoughtco\ABTester;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Blueprint;
use Statamic\Facades\File;
use Statamic\Facades\Folder;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Experiment implements Arrayable
{
    use FluentlyGetsAndSets;

    protected $handle;

    protected $title;

    protected $variants = [];

    protected $type;

    public function all()
    {
        return collect(Folder::getFilesByType(config('ab.experiments_path'), 'yaml'))->map(function ($file) {
            return (new Experiment)->find(pathinfo($file)['filename']);
        });
    }

    public function find($handle)
    {
        $this->handle($handle);

        if (! File::exists($this->path())) {
            throw new \Exception("Experiment [{$handle}] not found.");
        }

        return $this->hydrate();
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
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

    public function path()
    {
        return config('ab.experiments_path')."/{$this->handle()}.yaml";
    }

    public function resultsPath()
    {
        return config('ab.results_path')."/{$this->handle()}.yaml";
    }

    public function hydrate()
    {
        collect(YAML::parse(File::get($this->path())))
            ->filter(function ($value, $property) {
                return in_array($property, ['handle', 'title', 'type', 'variants']);
            })
            ->each(function ($value, $property) {
                $this->{$property} = $value;
            });

        return $this;
    }

    public function check($variant)
    {
        return $this->visitorVariant() !== $variant;
    }

    public function recordHit($variant)
    {
        $key = "{$variant}.hits";

        $this->hydrateResult($key);

        Cache::increment($this->cacheKey($key));

        return $this;
    }

    public function recordFailure($variant)
    {
        $key = "{$variant}.failed";

        $this->hydrateResult($key);

        Cache::increment($this->cacheKey($key));

        return $this;
    }

    public function recordSuccess($variant)
    {
        $key = "{$variant}.completed";

        $this->hydrateResult($key);

        Cache::increment($this->cacheKey($key));

        return $this;
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
                'validate' => ['required', 'alpha_dash'],
                'from' => 'title',
            ],
            'type' => [
                'type' => 'select',
                'validate' => 'required',
                'options' => [
                    'entry' => 'Entry',
                    'global' => 'Global',
                    'manual' => 'Manual'
                ],
                'max_items' => 1,
            ],
            'variants' => [
                'type' => 'grid',
                'mode' => 'stacked',
                'fields' => [
                    [
                        'handle' => 'slug',
                        'field' => [
                            'label' => __('Handle'),
                            'type' => 'slug',
                            'validate' => 'required',
                        ],
                    ],
                    [
                        'handle' => 'entry',
                        'field' => [
                            'label' => __('Entry'),
                            'type' => 'entries',
                            'mode' => 'default',
                            'max_items' => 1,
                            'validate' => 'required',
                            'if' => [
                                'root.type' => 'equals entry'
                            ],
                        ],
                    ],
                    [
                        'handle' => 'global',
                        'field' => [
                            'label' => __('Global - make custom field type'),
                            'type' => 'code',
                            'max_items' => 1,
                            'validate' => 'required',
                            'if' => [
                                'root.type' => 'equals global'
                            ],
                        ],
                    ],
                ],
                'validate' => 'array',
            ],
        ]);
    }

    public function create($formData)
    {
        $experiment = (new Experiment)
            ->handle($formData['handle'])
            ->title($formData['title'])
            ->type($formData['type'])
            ->variants($formData['variants']);

        return $experiment->save();
    }

    public function update($formData)
    {
        $originalPath = $this->path();

        $this->handle($formData['handle'])
            ->title($formData['title'])
            ->type($formData['type'])
            ->variants($formData['variants']);

        $this->save();

        if ($originalPath !== $this->path()) {
            // dump('rename file');
            // File::rename($originalPath, $this->path());
            // return cp_route('ab.experiments.edit', $this->handle());
        }
    }

    protected function save()
    {
        $data = $this->toArray();

        unset($data['handle']);

        File::put($this->path(), YAML::dump($data));

        return $this;
    }

    public function results($variant = null)
    {
        if (! is_null($variant)) {
            return $this->getResultsFor($variant);
        }


        return collect($this->variants())->map(function ($variant) {
            return $this->getResultsFor($variant['slug']);
        })->toArray();
    }

    public function persistResults()
    {
        $results = collect($this->results())->keyBy('id')->map(function ($variant) {
            return [
                'hits' => $variant['hits'],
                'completed' => $variant['completed'],
                'failed' => $variant['failed'],
            ];
        })->toArray();

        File::put($this->resultsPath(), YAML::dump($results));

        return $this;
    }

    protected function getResultsFor($variant)
    {
        $hitsKey = "{$variant}.hits";
        $completedKey = "{$variant}.completed";
        $failedKey = "{$variant}.failed";

        $this->hydrateResult($hitsKey);
        $this->hydrateResult($completedKey);
        $this->hydrateResult($failedKey);

        return [
            'id' => $variant,
            'hits' => Cache::get($this->cacheKey($hitsKey)),
            'completed' => Cache::get($this->cacheKey($completedKey)),
            'failed' => Cache::get($this->cacheKey($failedKey))
        ];
    }

    protected function hydrateResult($key)
    {
        if (! is_null(Cache::get($this->cacheKey($key)))) {
            return;
        }

        $results = YAML::parse(File::get($this->resultsPath()));

        Cache::forever($this->cacheKey($key), Arr::get($results, $key, '0'));

        return $this;
    }

    protected function visitorVariant()
    {
        return $this->variants()[$this->getVariantIndex()];
    }

    protected function getVariantIndex()
    {
        if (session()->has($sessionKey = "ab.{$this->handle()}.variant")) {
            return session($sessionKey);
        }

        $count = count($this->variants());
        $ip = rand(0, $count - 1); // request()->ip();
        $key = array_sum(explode('.', $ip));

        while (strlen($key) > strlen($count)) {
            $key = array_sum(str_split($key));
        }

        session()->put($sessionKey, $visitorVariant = $key % $count);

        return $visitorVariant;
    }

    protected function cacheKey($suffix)
    {
        return 'ab_experiment.'.$this->handle().Str::ensureLeft($suffix, '.');
    }

    public function fields()
    {
        return [
            'handle' => $this->handle,
            'title' => $this->title,
            'variants' => $this->variants,
            'type' => $this->type,
        ];
    }

    public function toArray()
    {
        return [
            'handle' => $this->handle,
            'title' => $this->title,
            'variants' => $this->variants,
            'type' => $this->type,
        ];
    }
}
