<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AutoSync extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('auto_sync', function (Blueprint $table) {
            $table->increments('id');

            $table->string('type', 10);
            $table->string('link', 250);
            $table->text('configuration');
            $table->time('time_1');
            $table->time('time_2')->nullable();
            $table->boolean('status')->default(false);
            $table->text('info');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('auto_sync');
    }
}
