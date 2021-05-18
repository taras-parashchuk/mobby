<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductVariantAttributeValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variant_attribute_values', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('attribute_value_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('main_id')->index();
            $table->unsignedBigInteger('attribute_id')->index();

            $table->foreign('attribute_value_id')->references('id')
                ->on('attribute_values')
                ->onDelete('cascade');

            $table->foreign('main_id')->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('product_id')->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('attribute_id')->references('id')
                ->on('attributes')
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
        Schema::dropIfExists('product_variant_attribute_values');
    }
}
