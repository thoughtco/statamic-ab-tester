<?php

namespace Thoughtco\StatamicABTester\Experiment\Stache;

use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;
use Thoughtco\StatamicABTester\Facades;

class ExperimentStore extends BasicStore
{
    public function key()
    {
        return 'experiments';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        // The structures themselves should only exist in the root
        // (ie. no slashes in the filename)
        $filename = Str::after(Path::tidy($file->getPathName()), $this->directory);

        return substr_count($filename, '/') === 0 && $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relative = Str::after($path, $this->directory);
        $id = Str::before($relative, '.yaml');

        $data = YAML::file($path)->parse($contents);

        return Facades\Experiment::make()
            ->id($id)
            ->title($data['title'] ?? '')
            ->type($data['type'] ?? '')
            ->goals($data['goals'] ?? [])
            ->results($data['results'] ?? [])
            ->startAt($data['start_at'] ?? null)
            ->endAt($data['end_at'] ?? null)
            ->published($data['published'] ?? false)
            ->data(Arr::except($data, ['title', 'type', 'goals', 'results', 'start_at', 'end_at', 'published']));
    }

    public function getItemKey($item)
    {
        return $item->id();
    }

    public function filter($file)
    {
        return $file->getExtension() === 'yaml';
    }
}
