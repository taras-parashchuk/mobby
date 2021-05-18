<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductToCompareCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_to_compare_categories', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('category_id')->index();

            $table->foreign('product_id')->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('category_id')->references('id')
                ->on('categories')
                ->onDelete('cascade');

            $table->unique(['product_id', 'category_id']);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_to_compare_categories');
    }
}
