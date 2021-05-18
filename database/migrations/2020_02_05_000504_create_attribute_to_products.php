<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_to_products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('attribute_id')->index();
            $table->unsignedBigInteger('product_id')->index();

            $table->boolean('main')->default(false);
            $table->boolean('has_variant')->default(false);

            $table->foreign('attribute_id')->references('id')
                ->on('attributes')
                ->onDelete('cascade');

            $table->foreign('product_id')->references('id')
                ->on('products')
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
        Schema::dropIfExists('attribute_to_products');
    }
}
