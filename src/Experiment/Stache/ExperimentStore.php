<?php

namespace Thoughtco\StatamicABTester\Experiment\Stache;

use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
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
        $handle = Str::before($relative, '.yaml');

        $data = YAML::file($path)->parse($contents);

        return Facades\Experiment::make()
            ->handle($handle)
            ->title($data['title'] ?? '')
            ->type($data['type'] ?? '')
            ->variants($data['variants'] ?? [])
            ->results($data['results'] ?? [])
            ->startAt($data['start_at'] ?? null)
            ->endAt($data['end_at'] ?? null);
    }

    public function getItemKey($item)
    {
        return $item->handle();
    }

    public function filter($file)
    {
        return $file->getExtension() === 'yaml';
    }
}
