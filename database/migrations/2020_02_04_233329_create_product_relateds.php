<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductRelateds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_relateds', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('recipient_id')->index();
            $table->unsignedBigInteger('source_id')->index();

            $table->foreign('recipient_id')->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('source_id')->references('id')
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
        Schema::dropIfExists('product_relateds');
    }
}
