<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderProductsExternalSourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products_external_source', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_products_id');
            $table->string('external_order_product_id');
            $table->string('external_type');
            $table->foreign('order_products_id')->references('id')->on('order_products')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_products_external_source');
    }
}
