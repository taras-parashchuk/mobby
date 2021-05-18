<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeValueTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_value_translations', function (Blueprint $table) {

            $table->unsignedBigInteger('attribute_value_id')->index();
            $table->string('locale', 5)->index();

            $table->string('value', 180);

            $table->foreign('attribute_value_id')->references('id')
                ->on('attribute_values')
                ->onDelete('cascade');

            $table->foreign('locale')->references('locale')
                ->on('languages')
                ->onDelete('cascade');

            $table->unique(['attribute_value_id', 'locale']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attribute_value_translations');
    }
}
