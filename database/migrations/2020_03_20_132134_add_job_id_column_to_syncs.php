<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJobIdColumnToSyncs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('syncs', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('job_id')->index()->nullable();

            $table->foreign('job_id')->references('id')->on('jobs')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('syncs', function (Blueprint $table) {
            //
            $table->dropColumn('job_id');
        });
    }
}
