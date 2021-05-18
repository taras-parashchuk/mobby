<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeValueToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_value_to_products', function (Blueprint $table) {
            $table->bigIncrements('id');


            $table->unsignedBigInteger('attribute_value_id')->index();
            $table->unsignedBigInteger('attribute_to_product_id')->index();

            $table->foreign('attribute_value_id')->references('id')
                ->on('attribute_values')
                ->onDelete('cascade');

            $table->foreign('attribute_to_product_id')->references('id')
                ->on('attribute_to_products')
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
        Schema::dropIfExists('attribute_value_to_products');
    }
}
