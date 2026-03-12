# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

**Testing:**
```bash
./vendor/bin/pest              # Run all tests
./vendor/bin/pest tests/path/to/SomeTest.php  # Run a single test file
./vendor/bin/pest --filter "test name"        # Run tests matching a name
```

**Frontend:**
```bash
npm run dev        # Start Vite dev server
npm run build      # Build assets to dist/
```

**Code style:**
```bash
./vendor/bin/pint  # Format PHP code (Laravel Pint)
```

## Architecture

This is a Statamic CMS addon (PHP/Laravel + Vue.js) for running A/B experiments on content.

### Dual Storage Pattern

Both `Experiment` and `Goal` entities support two storage drivers, selected via config:
- **Stache** (default): YAML file storage via Statamic's Stache system (`src/Experiment/Stache/`, `src/Goal/Stache/`)
- **Eloquent**: Database storage (`src/Experiment/Eloquent/`, `src/Goal/Eloquent/`)

Each driver implements the same `Repository` and `QueryBuilder` contracts from `src/Contracts/`. The `ServiceProvider` binds the active driver's implementations.

### Two Types of Experiments

1. **Item experiments** — linked to a Statamic content entry. The `ABTesterMiddleware` intercepts requests, detects an active experiment for the item, and overrides field values via Statamic's augmentation hooks. Variant data is stored directly on the experiment.

2. **Manual/field experiments** — configured via `ExperimentFields` custom fieldtype; the template tag `{{ ab:experiment }}` renders the appropriate variant in Antlers templates.

### Frontend Tracking

`src/Tags/ABTags.php` provides Antlers template tags:
- `{{ ab:experiment }}` — renders variant content
- `{{ ab:goal:completed }}` / `{{ ab:goal:failed }}` — records conversion events
- `{{ ab:js }}` — outputs JavaScript for frontend hit/conversion tracking

The `FrontendActionsController` handles the API endpoint that the JS tracker calls. Results are always stored in `ab_test_results` via `Models/AbTestResult.php` (Eloquent), regardless of the experiment/goal storage driver.

### Static Cache Integration

`src/StaticCaching/ABCacher.php` extends Statamic's static caching to handle per-visitor cache invalidation for pages with active experiments, so each user sees a consistent variant.

### Control Panel UI

Built with Inertia.js + Vue 3. Pages live in `resources/js/pages/`. The `ServiceProvider` registers CP nav items, permissions (`view experiments`, `edit experiments`, etc.), and CP routes from `routes/cp.php`.

**Always use Statamic's `ui-*` component library** for all CP output — never write raw Tailwind classes. These components are globally registered Vue components that render consistent Statamic-styled UI.

Available components (non-exhaustive):
- Layout: `ui-widget`, `ui-panel`, `ui-panel-header`, `ui-card`, `ui-header`
- Typography: `ui-heading`, `ui-description`
- Data: `ui-table`, `ui-table-columns`, `ui-table-column`, `ui-table-rows`, `ui-table-row`, `ui-table-cell`
- Inline: `ui-badge` (accepts `color` prop: `green`, `red`, `yellow`, `blue`, `gray`), `ui-button`
- Overlays: `ui-modal`, `ui-modal-close`

**Widget Blade views** are rendered via Statamic's `DynamicHtmlRenderer`, which compiles the HTML string as `defineComponent({ template: html })`. This means all `ui-*` components work in Blade widget views exactly as in Vue templates. Blade `@foreach`/`@if` are resolved server-side first; the resulting HTML (with `ui-*` tags intact) is then compiled by Vue client-side. Named slots (`<template #footer>`) also work in widget HTML for this reason.

### Key relationships

- `ServiceProvider` → wires everything together (routes, middleware, bindings, events, nav, permissions)
- `Experiment`/`Goal` abstract classes use PHP traits for data access patterns
- `Http/Resources/` transform models to JSON for Inertia responses
- `database/migrations/results/` always runs; `experiments/` and `goals/` migrations are only needed when using the Eloquent driver
