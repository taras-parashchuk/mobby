<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->bigIncrements('id');


            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('product_id')->index()->nullable();

            $table->string('sku', 100);
            $table->string('name', 240);
            $table->longText('specification');
            $table->decimal('quantity', 8, 2);
            $table->double('price');
            $table->double('special');

            $table->foreign('product_id')->references('id')
                ->on('products')
                ->onDelete('set null');

            $table->foreign('order_id')->references('id')
                ->on('orders')
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
        Schema::dropIfExists('order_products');
    }
}
