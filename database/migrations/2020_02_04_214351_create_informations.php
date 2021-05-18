<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInformations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('informations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('image', 200);
            $table->boolean('status')->default(true);
            $table->smallInteger('sort_order')->nullable()->default(0);
            $table->boolean('in_top')->default(true);
            $table->boolean('in_bottom')->default(true);
            $table->string('slug', 120)->index();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('informations');
    }
}
