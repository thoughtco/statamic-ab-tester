<?php

namespace Thoughtco\StatamicABTester\Goal\Stache;

use Statamic\Data\ExistsAsFile;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Facades\Stache;
use Statamic\Support\Arr;
use Thoughtco\StatamicABTester\Goal\Goal as BaseGoal;

class Goal extends BaseGoal
{
    use ExistsAsFile, TracksQueriedColumns, TracksQueriedRelations;

    public function path()
    {
        return $this->initialPath ?? $this->buildPath();
    }

    public function buildPath()
    {
        return vsprintf('%s/%s.%s', [
            rtrim(Stache::store('goals')->directory(), '/'),
            $this->id(),
            $this->fileExtension(),
        ]);
    }

    public function fileData()
    {
        return Arr::removeNullValues(array_merge($this->data->all(), [
            'id' => $this->id(),
            'handle' => $this->handle(),
            'title' => $this->title(),
        ]));
    }
}
