<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNovaPoshtaCities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nova_poshta_cities', function (Blueprint $table) {
            $table->string('ref', 180)->index();
            $table->string('value', 120);

            $table->string('area_ref', 180)->index();
            $table->foreign('area_ref')->references('ref')->on('nova_poshta_areas')
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
        Schema::dropIfExists('nova_poshta_cities');
    }
}
