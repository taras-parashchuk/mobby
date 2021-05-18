<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBreakedColumnToSyncs extends Migration
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
            $table->boolean('breaked', false);
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
            $table->dropColumn('breaked');
        });
    }
}
