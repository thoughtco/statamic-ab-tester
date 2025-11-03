<?php

namespace Thoughtco\StatamicABTester\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;
use Thoughtco\StatamicABTester\Contracts\Experiment;

class ExperimentDeleted extends Event implements ProvidesCommitMessage
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public Experiment $experiment) {}

    public function commitMessage()
    {
        return __('Experiment deleted', [], config('statamic.git.locale'));
    }
}
