<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductDiscounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_discounts', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('user_group_id')->index();

            $table->double('price', 8, 2);
            $table->double('quantity', 4, 2);


            $table->timestamp('date_start')->useCurrent();
            $table->timestamp('date_end')->nullable();

            $table->foreign('product_id')->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('user_group_id')->references('id')
                ->on('user_groups')
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
        Schema::dropIfExists('product_discounts');
    }
}
