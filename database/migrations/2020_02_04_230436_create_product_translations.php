<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_translations', function (Blueprint $table) {

            $table->unsignedBigInteger('product_id')->index();
            $table->string('locale', 5)->index();

            $table->string('name', 200);
            $table->text('description');
            $table->string('meta_title', 250)->nullable();
            $table->string('meta_h1', 250)->nullable();
            $table->string('meta_description', 250)->nullable();
            $table->string('meta_keywords', 250)->nullable();
            $table->string('warranty', 240)->nullable();


            $table->foreign('product_id')->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('locale')->references('locale')
                ->on('languages')
                ->onDelete('cascade');

            $table->unique(['product_id', 'locale']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_translations');
    }
}
