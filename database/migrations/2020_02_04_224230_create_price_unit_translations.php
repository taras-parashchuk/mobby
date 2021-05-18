<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceUnitTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_unit_translations', function (Blueprint $table) {

            $table->unsignedBigInteger('price_unit_id')->index();
            $table->string('locale', 5)->index();

            $table->string('name', 100);

            $table->foreign('price_unit_id')->references('id')
                ->on('price_units')
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
        Schema::dropIfExists('price_unit_translations');
    }
}
