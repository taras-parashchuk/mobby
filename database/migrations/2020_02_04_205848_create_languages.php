<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLanguages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name', 32);
            $table->string('locale', 5)->index();
            $table->smallInteger('sort_order')->default(0)->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('show_on_site')->default(true);
            $table->boolean('index')->default(true);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('languages');
    }
}
