
<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Thoughtco\StatamicABTester\Facades\Experiment;

beforeEach(function () {
    $this->actingAs(User::make()->makeSuper()->save());
});

describe('Experiments Controller', function () {
    it('displays experiments index', function () {
        $this->get(cp_route('ab.experiments.index'))
            ->assertOk();
    });

    it('returns experiments json', function () {
        $experiment = tap(Experiment::make('test-experiment')
            ->title('Test Experiment')
            ->published(true))
            ->save();

        $this->get(cp_route('ab.experiments.json'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'published',
                    ],
                ],
            ]);
    });

    it('shows experiment create form', function () {
        $this->get(cp_route('ab.experiments.create'))
            ->assertOk();
    });

    it('creates new experiment', function () {
        $this->assertCount(0, Experiment::all());

        $data = [
            'title' => 'Test Experiment',
            'type' => 'manual',
            'published' => true,
            'goals' => [1],
            'manual_fields' => [
                'control' => ['weight' => 50],
                'variant_a' => ['weight' => 50],
            ],
        ];

        $this->post(cp_route('ab.experiments.store'), $data)
            ->assertStatus(200);

        $this->assertCount(1, Experiment::all());
    });

    it('validates experiment creation', function () {
        $this->post(cp_route('ab.experiments.store'), [])
            ->assertSessionHasErrors(['title']);
    });

    it('shows experiment', function () {
        $experiment = tap(Experiment::make('test-experiment')
            ->title('Test Experiment'))
            ->save();

        $this->get(cp_route('ab.experiments.show', $experiment->id()))
            ->assertOk();
    });

    it('updates experiment', function () {
        $experiment = tap(Experiment::make('test-experiment')
            ->title('Original Title'))
            ->save();

        $data = [
            'title' => 'Updated Experiment',
            'type' => 'manual',
            'goals' => [1],
            'published' => false,
        ];

        $this->patch(cp_route('ab.experiments.update', $experiment->id()), $data)
            ->assertStatus(200);

        expect($experiment->fresh()->title())->toBe('Updated Experiment');
    });

    it('deletes experiment', function () {
        $experiment = tap(Experiment::make('test-experiment')
            ->title('Test Experiment'))
            ->save();

        $this->delete(cp_route('ab.experiments.delete', $experiment->id()))
            ->assertStatus(200);

        expect(Experiment::find($experiment->id()))->toBeNull();
    });

    it('processes experiment_fields values when creating item type experiment', function () {
        // Create blueprint with an integer field - process() casts strings to int
        $blueprint = Blueprint::make('article');
        $blueprint->setContents([
            'fields' => [
                ['handle' => 'title', 'field' => ['type' => 'text']],
                ['handle' => 'view_count', 'field' => ['type' => 'integer']],
            ],
        ])->setNamespace('collections.articles');
        $blueprint->save();

        // Create collection with blueprint
        $collection = Collection::make('articles');
        $collection->entryBlueprints(['article']);
        $collection->save();

        // Create entry
        $entry = tap(Entry::make()
            ->collection('articles')
            ->blueprint('article')
            ->slug('test-article')
            ->data([
                'title' => 'Original Title',
                'view_count' => 0,
            ]))
            ->save();

        $data = [
            'title' => 'Test Item Experiment',
            'type' => 'item',
            'item_id' => $entry->id(),
            'published' => true,
            'goals' => [1],
            'experiment_fields' => [
                'fields' => ['title', 'view_count'],
                'values' => [
                    'title' => 'Updated Title',
                    // Send as string - process() should convert to integer
                    'view_count' => '42',
                ],
            ],
        ];

        $this->post(cp_route('ab.experiments.store'), $data)
            ->assertStatus(200);

        $experiment = Experiment::all()->first();
        expect($experiment)->not->toBeNull();
        expect($experiment->type())->toBe('item');

        // Verify the experiment_fields values were processed
        $experimentFields = $experiment->get('experiment_fields');
        expect($experimentFields)->not->toBeNull();
        expect($experimentFields['values']['title'])->toBe('Updated Title');

        // The integer field should be processed - string "42" becomes int 42
        expect($experimentFields['values']['view_count'])->toBe(42);
        expect($experimentFields['values']['view_count'])->toBeInt();
    });

    it('processes experiment_fields values when updating item type experiment', function () {
        // Create blueprint with an integer field - process() casts strings to int
        $blueprint = Blueprint::make('article');
        $blueprint->setContents([
            'fields' => [
                ['handle' => 'title', 'field' => ['type' => 'text']],
                ['handle' => 'view_count', 'field' => ['type' => 'integer']],
            ],
        ])->setNamespace('collections.articles');
        $blueprint->save();

        // Create collection with blueprint
        $collection = Collection::make('articles');
        $collection->entryBlueprints(['article']);
        $collection->save();

        // Create entry
        $entry = tap(Entry::make()
            ->collection('articles')
            ->blueprint('article')
            ->slug('test-article')
            ->data([
                'title' => 'Original Title',
                'view_count' => 0,
            ]))
            ->save();

        // Create the experiment first
        $experiment = tap(Experiment::make('test-experiment')
            ->title('Test Experiment')
            ->type('item')
            ->goals([1])
            ->data([
                'item_id' => $entry->id(),
                'experiment_fields' => [
                    'fields' => ['title'],
                    'values' => ['title' => 'Initial Value'],
                ],
            ]))
            ->save();

        // Now update the experiment with new values
        $updateData = [
            'title' => 'Updated Experiment Title',
            'type' => 'item',
            'goals' => [1],
            'published' => false,
            'experiment_fields' => [
                'fields' => ['title', 'view_count'],
                'values' => [
                    'title' => 'New Updated Title',
                    // Send as string - process() should convert to integer
                    'view_count' => '99',
                ],
            ],
        ];

        $this->patch(cp_route('ab.experiments.update', $experiment->id()), $updateData)
            ->assertStatus(200);

        $updatedExperiment = $experiment->fresh();
        expect($updatedExperiment->title())->toBe('Updated Experiment Title');

        // Verify the experiment_fields values were processed
        $experimentFields = $updatedExperiment->get('experiment_fields');
        expect($experimentFields)->not->toBeNull();
        expect($experimentFields['values']['title'])->toBe('New Updated Title');

        // The integer field should be processed - string "99" becomes int 99
        expect($experimentFields['values']['view_count'])->toBe(99);
        expect($experimentFields['values']['view_count'])->toBeInt();
    });

    it('does not process experiment_fields for manual type experiments', function () {
        $data = [
            'title' => 'Manual Experiment',
            'type' => 'manual',
            'published' => true,
            'goals' => [1],
            'manual_fields' => [
                'control' => ['weight' => 50],
                'variant_a' => ['weight' => 50],
            ],
        ];

        $this->post(cp_route('ab.experiments.store'), $data)
            ->assertStatus(200);

        $experiment = Experiment::all()->first();
        expect($experiment)->not->toBeNull();
        expect($experiment->type())->toBe('manual');
        expect($experiment->get('experiment_fields'))->toBeNull();
    });
});
