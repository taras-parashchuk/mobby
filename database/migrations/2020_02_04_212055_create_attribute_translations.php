<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_translations', function (Blueprint $table) {
            $table->unsignedBigInteger('attribute_id')->index();
            $table->string('locale', 5)->index();

            $table->string('name', 50);
            $table->string('postfix', 16)->nullable();
            $table->string('summary', 140)->nullable();

            $table->foreign('attribute_id')->references('id')
                ->on('attributes')
                ->onDelete('cascade');

            $table->foreign('locale')->references('locale')
                ->on('languages')
                ->onDelete('cascade');

            $table->unique(['attribute_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attribute_translations');
    }
}
