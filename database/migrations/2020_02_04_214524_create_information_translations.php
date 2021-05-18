<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInformationTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('information_translations', function (Blueprint $table) {
            $table->unsignedBigInteger('information_id')->index();
            $table->string('locale', 5)->index();

            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('meta_title', 250)->nullable();
            $table->string('meta_description', 250)->nullable();
            $table->string('meta_keywords', 250)->nullable();

            $table->foreign('information_id')->references('id')
                ->on('informations')
                ->onDelete('cascade');

            $table->foreign('locale')->references('locale')
                ->on('languages')
                ->onDelete('cascade');

            $table->unique(['information_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('information_translations');
    }
}
