<?php

namespace Thoughtco\StatamicABTester\Experiment\Eloquent;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExperimentModel extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'completed_at',
        'data',
        'end_at',
        'goals',
        'experiment_fields',
        'manual_fields',
        'published',
        'start_at',
        'title',
        'type',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'data' => AsArrayObject::class,
        'end_at' => 'datetime',
        'experiment_fields' => AsArrayObject::class,
        'goals' => AsArrayObject::class,
        'manual_fields' => AsArrayObject::class,
        'published' => 'boolean',
        'start_at' => 'datetime',
    ];

    protected $table = 'experiments';
}
