<?php

namespace Thoughtco\ABTester;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Facades\Blueprint;
use Statamic\Facades\File;
use Statamic\Facades\Folder;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Experiment implements Arrayable
{
    use FluentlyGetsAndSets;

    protected $handle;

    protected $results = [];

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

        $this->hydrateResults();

        return $this;
    }

    public function recordHit($variant)
    {
        $this->results[$variant]['hits']++;

        $this->saveResults();

        return $this;
    }

    public function recordFailure($variant)
    {
        $this->results[$variant]['failed']++;

        $this->saveResults();

        return $this;
    }

    public function recordSuccess($variant)
    {
        $this->results[$variant]['successful']++;

        $this->saveResults();

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
                    'manual' => 'Manual',
                ],
                'max_items' => 1,
            ],
            'variants' => [
                'type' => 'grid',
                'mode' => 'stacked',
                'fields' => [
                    [
                        'handle' => 'label',
                        'field' => [
                            'label' => __('Label'),
                            'type' => 'text',
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
                                'root.type' => 'equals entry',
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
            return $this->getResultsFor($variant['id']);
        })->toArray();
    }

    public function saveResults()
    {
        File::put($this->resultsPath(), YAML::dump($this->results));

        return $this;
    }

    protected function getResultsFor($variant)
    {
        return array_merge(['label' => Arr::get($this->variants()->firstWhere('id', $variant) ?? [], 'label', $variant)], $this->results[$variant]);
    }

    protected function hydrateResults()
    {
        $this->results = YAML::file($this->resultsPath())->parse() ?? [];

        $this->variants()->each(function ($variant) {
            if (! Arr::has($this->results, $variant['id'].'.hits')) {
                Arr::set($this->results, $variant['id'].'.hits', 0);
            }

            if (! Arr::has($this->results, $variant['id'].'.successful')) {
                Arr::set($this->results, $variant['id'].'.successful', 0);
            }

            if (! Arr::has($this->results, $variant['id'].'.failed')) {
                Arr::set($this->results, $variant['id'].'.failed', 0);
            }
        });

        return $this;
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
