<?php

namespace Thoughtco\StatamicABTester\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;
use Thoughtco\StatamicABTester\Contracts\Goal;

class GoalDeleted extends Event implements ProvidesCommitMessage
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public Goal $goal) {}

    public function commitMessage()
    {
        return __('Goal deleted', [], config('statamic.git.locale'));
    }
}
