<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeNextTypeColumnsToSyncJobs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sync_jobs', function (Blueprint $table) {
            //
            $table->string('type', '30')->nullable()->index()->default(null);

            $table->string('next_type', '30')->nullable()->index()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sync_jobs', function (Blueprint $table) {
            //
            $table->dropColumn('type');

            $table->dropColumn('next_type');
        });
    }
}
