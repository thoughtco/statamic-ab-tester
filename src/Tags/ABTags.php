<?php

namespace Thoughtco\ABTester\Tags;

use Statamic\Facades;
use Statamic\Tags\Tags;
use Thoughtco\ABTester\Facades\Experiment;

class ABTags extends Tags
{
    protected static $handle = 'ab';

    public function index()
    {
        if (! $handle = $this->params->pull('experiment')) {
            return $this->parse();
        }

        if (! $experiment = Experiment::find($handle)) {
            return $this->parse();
        }

        $variants = $experiment->variants();

        if ($variantHandle = session()->get('statamic.ab.'.$handle)) {
            $variant = $variants->firstWhere('slug', $variantHandle);
        }

        if (! $variant) {
            if (!$variant = $variants->random()) {
                return $this->parse();
            }
        }

        $variantHandle = $variant['slug'];

        $experiment->recordHit($variantHandle);

        if ($this->params->bool('session')) {
            session()->put('statamic.ab.'.$handle, $variantHandle);
        }

        $mergeData = match ($experiment->type()) {
            'global' => ['no idea' => 'yet'],
            'entry' => ['entry' => Facades\Entry::find($variant['entry'])],
            default => [],
        };

        return $this->parse(array_merge($mergeData, [
            'experiment' => $handle,
            'variant' => $variantHandle, // $variant['handle'] ??
        ]));
    }

    public function failure()
    {
        if (! $experimentHandle = $this->params->pull('experiment')) {
            return $this->parse();
        }

        if (! $variantHandle = $this->params->pull('variant')) {
            return $this->parse();
        }

        if (! $experiment = Experiment::find($experimentHandle)) {
            return $this->parse();
        }

        $experiment->recordFailure($variantHandle);

        if (! $this->isPair) {
            return;
        }

        return $this->parse([
            'experiment' => $handle,
            'variant' => $variant, // $variant['handle'] ??
        ]);
    }

    public function success()
    {
        if (! $experimentHandle = $this->params->pull('experiment')) {
            return $this->parse();
        }

        if (! $variantHandle = $this->params->pull('variant')) {
            return $this->parse();
        }

        if (! $experiment = Experiment::find($experimentHandle)) {
            return $this->parse();
        }

        $experiment->recordSuccess($variantHandle);

        if (! $this->isPair) {
            return;
        }

        return $this->parse([
            'experiment' => $handle,
            'variant' => $variant, // $variant['handle'] ??
        ]);
    }
}
