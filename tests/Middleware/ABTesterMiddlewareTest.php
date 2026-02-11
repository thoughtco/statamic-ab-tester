<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Illuminate\Http\Request;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Http\Middleware\ABTesterMiddleware;

describe('AB Tester Middleware', function () {
    it('processes request without experiments', function () {
        $middleware = new ABTesterMiddleware;
        $request = Request::create('/');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
    });

    it('handles requests with active experiments', function () {
        $experiment = Experiment::make('test-experiment')
            ->title('Test Experiment')
            ->type('manual')
            ->published(true)
            ->data([
                'manual_fields' => [['handle' => 'variant_a', 'label' => 'Variant A']],
            ])
            ->save();

        $middleware = new ABTesterMiddleware;
        $request = Request::create('/');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
    });

    it('selects variant 2 with asset fields in replicator and returns valid asset reference', function () {
        // Create asset container
        $container = tap(AssetContainer::make('assets')
            ->disk('local'))
            ->save();

        // Create asset
        $asset = tap(Asset::make()
            ->container('assets')
            ->path('test-image.jpg'))
            ->save();

        // Create blueprint with replicator field containing asset field
        $blueprint = Blueprint::make('page');
        $blueprint
            ->setContents([
                'fields' => [
                    [
                        'handle' => 'title',
                        'field' => ['type' => 'text'],
                    ],
                    [
                        'handle' => 'content_blocks',
                        'field' => [
                            'type' => 'replicator',
                            'sets' => [
                                'image_block' => [
                                    'fields' => [
                                        [
                                            'handle' => 'image',
                                            'field' => [
                                                'type' => 'assets',
                                                'container' => 'assets',
                                                'max_files' => 1,
                                            ],
                                        ],
                                        [
                                            'handle' => 'caption',
                                            'field' => ['type' => 'text'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->setNamespace('collections.pages');

        $blueprint->save();

        // Create collection with blueprint
        $collection = Collection::make('pages');
        $collection->entryBlueprints(['page']);
        $collection->save();

        $entry = tap(Entry::make()
            ->collection('pages')
            ->blueprint('page')
            ->slug('test-page')
            ->data([
                'title' => 'Original Title',
                'content_blocks' => [
                    [
                        'type' => 'image_block',
                        'image' => $asset->id(),
                        'caption' => 'Original Caption',
                    ],
                ],
            ]))
            ->save();

        // Create experiment with variant 2 that includes asset field in replicator
        $experiment = tap(Experiment::make('test-experiment')
            ->title('Test Experiment')
            ->type('item')
            ->published(true)
            ->data([
                'item_id' => $entry->id(),
                'experiment_fields' => [
                    'values' => [
                        'title' => 'Updated Title',
                    ],
                ],
            ]))
            ->save();

        // Force variant 2 selection
        session()->put('statamic.ab.'.$experiment->id(), 2);

        $middleware = new ABTesterMiddleware;
        $request = Request::create('/');

        $response = $middleware->handle($request, function ($req) use ($entry, $asset) {
            $augmented = $entry->toAugmentedCollection();

            // Verify variant 2 data is applied
            expect($augmented->get('title')->value())->toBe('Updated Title');

            // Verify replicator has been updated
            $contentBlocks = $augmented->get('content_blocks')->value();
            expect($contentBlocks)->toHaveCount(1);

            $block = $contentBlocks[0];
            //expect($block['caption'])->toBe('Updated Caption');

            // Verify asset reference is valid and correct
            dd($block);
            //expect($block['image'])->not->toBeNull();
            $imageAsset = $block['image'];
            dd($imageAsset);
            expect($imageAsset->path())->toBe('test-image.jpg');
            expect($imageAsset->container()->handle())->toBe('assets');

            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
        expect($response->headers->get('X-ABTester-Experiments'))->toContain($experiment->id().':2');
    });

});
