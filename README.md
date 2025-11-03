# A/B Tester

A Statamic addon that allows you to setup and run A/B testing on your sites.

## 📋 Table of Contents
- [Commercial addon](#commercial-addon)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Creating Goals](#creating-goals)
    - [Creating Experiments](#creating-experiments)
    - [Experiment Types](#experiment-types)
    - [Viewing Results](#viewing-results)
- [API Reference](#api-reference)

## Commercial addon

⚠️ **Important**: This addon is paid software. You may use it for free during development, but you must purchase a license from the [Statamic Marketplace](https://statamic.com/addons/thoughtco/statamic-ab-tester) before deploying to production.

## Installation

Install via Composer:

`composer require thoughtco/statamic-ab-tester`

Then run the migration to create the required database table:

`php artisan migrate`

## Quick Start

1. **Create a Goal**: Set up what you want to measure (e.g., "Newsletter Signup")
2. **Create an Experiment**: Define what you want to test (e.g., different button colors)
3. **Track Results**: Monitor performance in the control panel
4. **Apply Winner**: Once you have a clear winner, apply the results


## Configuration

### Custom Database Connection

By default, this addon uses your default database connection and creates a table called `ab_test_results`. To use a custom connection, add this to your `.env` file:

`AB_TESTER_RESULTS_CONNECTION=your-connection`

### Static caching
If you are using the `half` caching strategy, switch to using the provided `ab` driver - this extends half caching but allows ab experiments to continue working.

If you are using `full` static caching, you will need to wrap any experiments in `{{ nocache }}` tags and ensure you are using the tags to record hits, successes and failures.


### Field selection
By default, all fields will be selectable to apply an A/B Test, but you can control this using the `Allow this field to be A/B tested` config field that this add-on provides in the config for each fieldtype.

If it makes sense for you to default to fields __not__ being included, you can set the `statamic-ab-tester.blueblueprint_fields_approach` to be 'opt-out'.


#
## Usage

### Creating Goals

The first step is to create a goal through the UI. Name it whatever you want (for example "Mailing List Signup" or "Add to Basket") and give it a unique handle.

Now you want to trigger that handle in your code - to do that simply call:
`\Thoughtco\StatamicABTester\Facades\Goal::completed('your-goal-handle')`

If your goal is completed on a page, you may just want to use the antlers tag:

`{{ ab:goal:completed handle="your-goal-handle" }}`

Alongside the handle, we also log the time the goal was completed, the user's IP, and their ID if they are logged in.

You can also (optionally) record failures, if your test requires it:

`\Thoughtco\StatamicABTester\Facades\Goal::failed('your-goal-handle')`

`{{ ab:goal:failed handle="your-goal-handle" }}`

#### Javascript helper

If you need to trigger goals from Javascript, include the `{{ ab:js }}` in your layout, then call:

`abTester.hit('experiment-id', { custom: 'data' })` to register a hit on an experiment

`abTester.completed('goal-id', { custom: 'data' })` to register a goal success

`abTester.failed('goal-id', { custom: 'data' })` to register a goal failure

### Creating Experiments

Next you need to make an experiment, which varies something on your site. To do this use the "Create A/B Experiment" action available on an entry.

This will open a modal allowing you to choose what field(s) you want to vary and define their alternate values.

Finally, ensure you associate them with the goal you created in the first step.

#### Outputting experiments

This add-on will automate the display of the variants, knowing when the item is augmented and switching it as appropriate.

If you are using full static caching, Statamic is never booted, so you will need to use `{{ nocache }}` alongside the tags this addon provides to run your experiments.

### Experiment Types

There are two types of experiments you can run:

#### Item

An Item experiment lets you select an entry and modify its content from the base entry. These can be created using the `Create A/B Experiment` action on the entry view.

#### Manual

A Manual experiment lets you determine what you want to do inside the experiment, e.g. show a different nav UI, show a different button style. You can use the `variant:handle` to determine what to show to the user.

### Viewing Results

Results can be viewed within the control panel.

Go to the listing view under "A/B Experiments", click on the "View" link for your experiment and you will be presented with a table showing your variations alongside their hits, successes, failures and success rates.

#### Applying results

If you want to apply one of your variants as the winner, simply click "Complete Experiment", and click "Apply" beside the variant you want to mark as the winner. The experiment will be marked as completed and will no longer be used for testing on your front end site.

If it is an "Item" experiment, the winner's values will be applied to the Entry.

## API Reference

### Tags
This package provides tags that you can use in your Statamic templates:

#### ab
This tag sets an A/B test for the given handle. It will randomly choose a experiment variant, record a hit and provide the experiment and handle to the content of the tag.

If you want your variant to persist over the session lifetime, set session="true"

```antlers 
{{ ab experiment="experiment_id" session="true" }} {{ experiment:title}} {{ variant }} {{ /ab }}
```

#### ab:success
This tag marks an A/B test as successful.

```antlers 
{{ ab:success experiment="experiment_id" variant="variant_handle" }}
```

or if you've used session="true" on the ab tag:

```antlers 
{{ ab:success experiment="experiment_id" from_session="true" }}
```

#### ab:failure
This tag marks an A/B test as a failure.

```antlers 
{{ ab:failure experiment="experiment_id" variant="variant_handle" }}
```

or if you've used session="true" on the ab tag:

```antlers 
{{ ab:failure experiment="experiment_id" from_session="true" }}
```

### Experiment Facade
This package provides a facade for interacting with experiments:
`\Thoughtco\StatamicABTester\Facades\Experiment`

#### get an experiment
`$experiment = \Thoughtco\StatamicABTester\Facades\Experiment::find('experiment_handle');`

#### get all experiments
`\Thoughtco\StatamicABTester\Facades\Experiment::all();`

#### query experiments
`\Thoughtco\StatamicABTester\Facades\Experiment::query()->where('title', 'test')->get();`


### Experiment
Once you have an experiment you can record hits, successes, failures and get results.

#### hit
Mark an experiment as being viewed:
`$experiment->recordHit($variantHandle, $customData = []);`

#### success
Mark an experiment as being successful:
`$experiment->recordSuccess($variantHandle, $goalId = null, $customData = []);`

#### failure
Mark an experiment as being a failure:
`$experiment->recordFailure($variantHandle, $goalId = null, $customData = []);`

#### results
Get the results of an experiment:
`$experiment->resultsQuery()->get();`


### Goal Facade
This package provides a facade for interacting with experiments:
`\Thoughtco\StatamicABTester\Facades\Goal`

#### get a goal
`$goal = \Thoughtco\StatamicABTester\Facades\Goal::find('goal_handle');`

#### get all goals
`\Thoughtco\StatamicABTester\Facades\Goal::all();`

#### query goals
`\Thoughtco\StatamicABTester\Facades\Goal::query()->where('title', 'test')->get();`

#### complete a goal
Mark an goal as being completed:
`\Thoughtco\StatamicABTester\Facades\Goal::completed($goalHandle, $customData = []);`

#### fail a goal
Mark an goal as being failed:
`\Thoughtco\StatamicABTester\Facades\Goal::failed($goalHandle, $customData = []);`

#### results
Get the results of an experiment:
`\Thoughtco\StatamicABTester\Facades\Goal::find('goal_handle')?->resultsQuery()->get();`
