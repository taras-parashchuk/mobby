<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNovaPoshtaDepartments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nova_poshta_departments', function (Blueprint $table) {
            $table->string('ref', 180)->index();
            $table->string('value', 120);

            $table->string('city_ref', 180)->index();
            $table->foreign('city_ref')->references('ref')
                ->on('nova_poshta_cities')
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
        Schema::dropIfExists('nova_poshta_departments');
    }
}
