<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_translations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('location_id')->index();
            $table->string('locale', 5)->index();

            $table->string('name', 250);
            $table->string('address', 250);
            $table->text('schedule')->nullable();

            $table->foreign('location_id')->references('id')
                ->on('locations')
                ->onDelete('cascade');

            $table->foreign('locale')->references('locale')
                ->on('languages')
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
        Schema::dropIfExists('location_translations');
    }
}
