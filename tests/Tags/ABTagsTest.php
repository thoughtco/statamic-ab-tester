<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Facades;
use Thoughtco\StatamicABTester\Experiment\Stache\Experiment;

it('returns a variant', function () {
    (new Experiment)
        ->handle('test')
        ->title('Test')
        ->type('manual')
        ->variants([
            ['id' => 'one', 'label' => 'One'],
            //['id' => 'two', 'label' => 'One'],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ variant:id }}{{ /ab }}');

    $this->assertSame($content, 'one');
});

it('returns an entry', function () {
    $collection = tap(Facades\Collection::make()
        ->handle('test')
        ->title('Test'))
        ->save();

    Facades\Entry::make()
        ->id('one')
        ->collection($collection)
        ->data(['title' => 'Test'])
        ->save();

    (new Experiment)
        ->handle('test')
        ->title('Test')
        ->type('entry')
        ->variants([
            ['id' => 'one', 'label' => 'One', 'entry' => 'one'],
            //['id' => 'two', 'label' => 'One'],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ entry:title }}{{ /ab }}');

    $this->assertSame($content, 'Test');
});

it('does nothing when start date is in the future', function () {
    (new Experiment)
        ->handle('test')
        ->title('Test')
        ->type('manual')
        ->startAt(now()->addDays(1))
        ->variants([
            ['id' => 'one', 'label' => 'One'],
            //['id' => 'two', 'label' => 'One'],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ variant:id }}{{ /ab }}');

    $this->assertSame($content, '');
});

it('works when start date is in the past', function () {
    (new Experiment)
        ->handle('test')
        ->title('Test')
        ->type('manual')
        ->startAt(now()->subDays(1))
        ->variants([
            ['id' => 'one', 'label' => 'One'],
            //['id' => 'two', 'label' => 'One'],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ variant:id }}{{ /ab }}');

    $this->assertSame($content, 'one');
});

it('does nothing when end date is in the past', function () {
    (new Experiment)
        ->handle('test')
        ->title('Test')
        ->type('manual')
        ->endAt(now()->subDays(1))
        ->variants([
            ['id' => 'one', 'label' => 'One'],
            //['id' => 'two', 'label' => 'One'],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ variant:id }}{{ /ab }}');

    $this->assertSame($content, '');
});

it('works when start date is in the future', function () {
    (new Experiment)
        ->handle('test')
        ->title('Test')
        ->type('manual')
        ->endAt(now()->addDays(1))
        ->variants([
            ['id' => 'one', 'label' => 'One'],
            //['id' => 'two', 'label' => 'One'],
        ])
        ->save();

    $content = (string) Facades\Antlers::parse('{{ ab experiment="test" }}{{ variant:id }}{{ /ab }}');

    $this->assertSame($content, 'one');
});
