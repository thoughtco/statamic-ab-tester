<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Facades;
use Thoughtco\StatamicABTester\Experiment\Stache\Experiment;

it('returns a variant', function () {
    (new Experiment)
        ->id('test')
        ->title('Test')
        ->type('manual')
        ->data([
            'manual_fields' => [['handle' => 'one', 'label' => 'One']],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ variant }}{{ /ab }}');

    $this->assertSame($content, 'one');
});

it('does nothing when start date is in the future', function () {
    (new Experiment)
        ->id('test')
        ->title('Test')
        ->type('manual')
        ->startAt(now()->addDays(1))
        ->data([
            'manual_fields' => [['label' => 'One', 'handle' => 'one']],
            // ['id' => 'two', 'label' => 'One'],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ variant }}{{ /ab }}');

    $this->assertSame($content, '');
});

it('works when start date is in the past', function () {
    (new Experiment)
        ->id('test')
        ->title('Test')
        ->type('manual')
        ->startAt(now()->subDays(1))
        ->data([
            'manual_fields' => [['label' => 'One', 'handle' => 'one']],
            // ['id' => 'two', 'label' => 'One'],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ variant }}{{ /ab }}');

    $this->assertSame($content, 'one');
});

it('does nothing when end date is in the past', function () {
    (new Experiment)
        ->id('test')
        ->title('Test')
        ->type('manual')
        ->endAt(now()->subDays(1))
        ->data([
            'manual_fields' => [['label' => 'One', 'handle' => 'one']],
            // ['id' => 'two', 'label' => 'One'],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ variant }}{{ /ab }}');

    $this->assertSame($content, '');
});

it('works when start date is in the future', function () {
    (new Experiment)
        ->id('test')
        ->title('Test')
        ->type('manual')
        ->endAt(now()->addDays(1))
        ->data([
            'manual_fields' => [['label' => 'One', 'handle' => 'one']],
            // ['id' => 'two', 'label' => 'One'],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ variant }}{{ /ab }}');

    $this->assertSame($content, 'one');
});

it('completes a goal', function () {
    $string = (string) Facades\Antlers::parse('{{ ab:goal:completed handle="test" }}');

    $this->assertStringContainsString('<script>abTester.completed', $string);
});

it('fails a goal', function () {
    $string = (string) Facades\Antlers::parse('{{ ab:goal:failed handle="test" }}');

    $this->assertStringContainsString('<script>abTester.failed', $string);
});
