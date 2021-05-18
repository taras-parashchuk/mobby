<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyTypeAndAddColumnResultCodeToSyncJobs extends Migration
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
            $table->dropColumn('next_type');
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
            $table->string('next_type', 100);
        });
    }
}
