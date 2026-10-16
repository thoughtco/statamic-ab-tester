<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ab_test_results', function (Blueprint $table) {
            $table->string('visitor_id')->nullable()->index()->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('ab_test_results', function (Blueprint $table) {
            $table->dropColumn('visitor_id');
        });
    }
};
