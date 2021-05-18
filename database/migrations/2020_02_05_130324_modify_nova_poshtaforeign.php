<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyNovaPoshtaforeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nova_poshta_cities', function (Blueprint $table) {
            //
            $table->dropForeign(['area_ref']);
        });

        Schema::table('nova_poshta_departments', function (Blueprint $table) {
            //
            $table->dropForeign(['city_ref']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nova_poshta_cities', function (Blueprint $table) {
            //
            $table->string('area_ref', 180)->index();
            $table->foreign('area_ref')->references('ref')->on('nova_poshta_areas')
                ->onDelete('cascade');
        });

        Schema::table('nova_poshta_departments', function (Blueprint $table) {
            //
            $table->string('city_ref', 180)->index();
            $table->foreign('city_ref')->references('ref')
                ->on('nova_poshta_cities')
                ->onDelete('cascade');
        });


    }
}
