<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWishlists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('product_id')->index();

            $table->foreign('user_id')->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('product_id')->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->timestamp('date_added')->useCurrent();

            $table->unique(['product_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wishlists');
    }
}
