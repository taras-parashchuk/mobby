<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyPasswordLengthForExternalApis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('externals_api', function (Blueprint $table) {
            //
            $table->string('password', 220)->change();
            $table->string('login', 220)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('externals_api', function (Blueprint $table) {
            //
        });
    }
}
