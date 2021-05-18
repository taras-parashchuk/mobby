<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_group_id')->index();
            $table->unsignedBigInteger('product_id')->index();

            $table->decimal('price_min', 8, 2);
            $table->decimal('price_max', 8, 2);
            $table->decimal('price', 8, 2);

            $table->foreign('user_group_id')->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('product_id')->references('id')
                ->on('products')
                ->onDelete('cascade');

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
        Schema::dropIfExists('product_prices');
    }
}
