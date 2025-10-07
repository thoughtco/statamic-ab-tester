<?php

namespace Thoughtco\StatamicABTester\Goal\Stache;

use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;
use Thoughtco\StatamicABTester\Facades;

class GoalStore extends BasicStore
{
    public function key()
    {
        return 'goals';
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

        return Facades\Goal::make()
            ->id($id)
            ->handle($data['handle'] ?? '')
            ->title($data['title'] ?? '')
            ->data(Arr::except($data, ['title']));
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
