<?php

namespace Thoughtco\StatamicABTester\Experiment\Stache;

use Statamic\Data\ExistsAsFile;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Facades\Stache;
use Statamic\Support\Arr;
use Thoughtco\StatamicABTester\Experiment\Experiment as BaseExperiment;

class Experiment extends BaseExperiment
{
    use ExistsAsFile, TracksQueriedColumns, TracksQueriedRelations;

    public function path()
    {
        return $this->initialPath ?? $this->buildPath();
    }

    public function buildPath()
    {
        return vsprintf('%s/%s.%s', [
            rtrim(Stache::store('experiments')->directory(), '/'),
            $this->handle(),
            $this->fileExtension(),
        ]);
    }

    public function fileData()
    {
        return Arr::removeNullValues([
            'handle' => $this->handle(),
            'title' => $this->title(),
            'variants' => $this->variants,
            'results' => $this->results,
            'start_at' => $this->startAt,
            'end_at' => $this->endAt,
        ]);
    }
}
