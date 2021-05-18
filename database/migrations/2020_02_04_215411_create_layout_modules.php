<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLayoutModules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('layout_modules', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('position', 50);
            $table->smallInteger('sort_order')->nullable()->default(0);

            $table->unsignedBigInteger('module_id')->index();
            $table->unsignedBigInteger('layout_id')->index();

            $table->foreign('module_id')->references('id')
                ->on('modules')
                ->onDelete('cascade');

            $table->foreign('layout_id')->references('id')
                ->on('layouts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('layout_modules');
    }
}
