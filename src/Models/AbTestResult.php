<?php

namespace Thoughtco\StatamicABTester\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\User;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Facades\Goal;

class AbTestResult extends Model
{
    protected $fillable = ['data', 'experiment_id', 'goal_id', 'ip_address', 'type', 'user_id'];

    public function getConnectionName()
    {
        if (! $connection = config('statamic-ab-tester.results.database_connection')) {
            return parent::getConnectionName();
        }

        return $connection;
    }

    protected function casts(): array
    {
        return [
            'data' => AsArrayObject::class,
        ];
    }

    public function experiment(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->experiment_id) {
                    return null;
                }

                return Experiment::find($this->experiment_id);
            }
        );
    }

    public function goal(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->goal_id) {
                    return null;
                }

                return Goal::find($this->goal_id);
            }
        );
    }

    public function user(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->user_id) {
                    return null;
                }

                return User::find($this->user_id);
            }
        );
    }
}
