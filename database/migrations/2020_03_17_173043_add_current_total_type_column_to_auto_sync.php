<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrentTotalTypeColumnToAutoSync extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auto_sync', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('current')->default(0)->nullable();
            $table->unsignedBigInteger('total')->default(0)->nullable();
            $table->string('data_type', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auto_sync', function (Blueprint $table) {
            //
            $table->dropColumn('current');
            $table->dropColumn('total');
            $table->dropColumn('data_type');
        });
    }
}
