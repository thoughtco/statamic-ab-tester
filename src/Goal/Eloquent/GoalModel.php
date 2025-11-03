<?php

namespace Thoughtco\StatamicABTester\Goal\Eloquent;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GoalModel extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'handle',
        'title',
        'data',
    ];

    protected $casts = [
        'data' => AsArrayObject::class,
    ];

    protected $table = 'goals';
}
