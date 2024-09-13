<?php

namespace Thoughtco\StatamicABTester\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;

class ExperimentCreated extends Event implements ProvidesCommitMessage
{
    use InteractsWithSockets, SerializesModels;

    public $experiment;

    public function __construct($experiment)
    {
        $this->experiment = $experiment;
    }

    public function commitMessage()
    {
        return __('Experiment created', [], config('statamic.git.locale'));
    }
}
