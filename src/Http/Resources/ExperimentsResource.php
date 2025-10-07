<?php

namespace Thoughtco\StatamicABTester\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Statamic\CP\Column;
use Statamic\CP\Columns;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;

class ExperimentsResource extends ResourceCollection
{
    use HasRequestedColumns;

    public $collects = ExperimentResource::class;

    protected $columns;

    protected $columnPreferenceKey;

    public function setColumnPreferenceKey(string $key): self
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    public function setColumns(): self
    {
        $columns = new Columns;

        $column = Column::make('title')
            ->listable(true)
            ->visible(true)
            ->defaultVisibility(true)
            ->defaultOrder(1)
            ->sortable(true);

        $columns->put('title', $column);

        $column = Column::make('id')
            ->label('ID')
            ->listable(true)
            ->visible(false)
            ->defaultVisibility(false)
            ->defaultOrder(2)
            ->sortable(true);

        $columns->put('id', $column);

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();

        return $this;
    }

    public function toArray($request): Collection
    {
        $this->setColumns();

        return $this->collection;
    }

    public function with($request): array
    {
        return [
            'meta' => [
                'columns' => $this->visibleColumns(),
            ],
        ];
    }
}
