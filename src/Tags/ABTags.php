<?php

namespace Thoughtco\StatamicABTester\Tags;

use Statamic\Facades;
use Statamic\Support\Str;
use Statamic\Tags\Tags;
use Thoughtco\StatamicABTester\Experiment\Stache\Experiment as ExperimentModel;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Facades\Goal;

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

        if ($experiment->startAt() && now()->isBefore($experiment->startAt())) {
            return $this->parse();
        }

        if ($experiment->endAt() && now()->isAfter($experiment->endAt())) {
            return $this->parse();
        }

        $variants = $experiment->variants();

        $useSession = $this->params->bool('session');

        $variant = false;
        if ($useSession && ($variantHandle = session()->get('statamic.ab.'.$handle))) {
            $variant = $this->variantFromHandle($experiment, $variantHandle);
        }

        if (! $variant) {
            if (! $variant = $variants->random()) {
                return $this->parse();
            }
        }

        $variantHandle = $variant['id'];

        $experiment->recordHit($variantHandle);

        if ($useSession) {
            session()->put('statamic.ab.'.$handle, $variantHandle);
        }

        $mergeData = match ($experiment->type()) {
            'global' => ['no idea' => 'yet'],
            'entry' => ['entry' => Facades\Entry::find($variant['entry'])],
            default => [],
        };

        return $this->parse(array_merge($mergeData, [
            'experiment' => $handle,
            'variant' => $this->variantFromHandle($experiment, $variantHandle),
        ]));
    }

    public function js()
    {
        return "
        <script>
        const abTester = {
            hit: (experiment, data) => abTester.run('hit', experiment, data),
            success: (goal, data) => abTester.run('success', goal, data),
            failure: (goal, data) => abTester.run('failure', goal, data),

            run: (type, source, data) => {
                fetch('".route('statamic.ab-tester.front-end-js')."', {
                    method: 'POST',
                    body: JSON.stringify({
                        type: type,
                        source: source,
                        data: data,
                    })
                });
            }
        }
        </script>
        ";
    }

    public function failure()
    {
        if (! $experimentHandle = $this->params->pull('experiment')) {
            return $this->parse();
        }

        $variantHandle = false;
        if ($this->params->bool('from_session')) {
            if (! $variantHandle = session()->get('statamic.ab.'.$experimentHandle)) {
                return $this->parse();
            }
        }

        if (! $variantHandle) {
            if (! $variantHandle = $this->params->pull('variant')) {
                return $this->parse();
            }
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
            'variant' => $this->variantFromHandle($experiment, $variantHandle),
        ]);
    }

    public function success()
    {
        if (! $experimentHandle = $this->params->pull('experiment')) {
            return $this->parse();
        }

        $variantHandle = false;
        if ($this->params->bool('from_session')) {
            if (! $variantHandle = session()->get('statamic.ab.'.$experimentHandle)) {
                return $this->parse();
            }
        }

        if (! $variantHandle) {
            if (! $variantHandle = $this->params->pull('variant')) {
                return $this->parse();
            }
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
            'variant' => $this->variantFromHandle($experiment, $variantHandle),
        ]);
    }

    private function variantFromHandle(ExperimentModel $experiment, string $variantHandle): ?array
    {
        return $experiment->variants()->firstWhere('id', $variantHandle);
    }

    public function wildcard($tag)
    {
        if (! Str::contains($tag, ':')) {
            return;
        }

        if (Str::before($tag, ':') == 'goal') {
            if (! $handle = $this->params->pull('handle')) {
                return;
            }

            match (Str::after($tag, ':')) {
                'completed' => Goal::completed($handle),
                'failed' => Goal::failed($handle),
                default => false
            };

            return;
        }
    }
}
