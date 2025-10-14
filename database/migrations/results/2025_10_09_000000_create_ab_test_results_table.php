<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ab_test_results', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->string('goal_id')->nullable()->index();
            $table->string('experiment_id')->index();
            $table->string('variation')->nullable()->index();
            $table->string('user_id')->nullable()->index();
            $table->ipAddress('ip_address')->nullable();
            $table->jsonb('data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ab_test_results');
    }
};
