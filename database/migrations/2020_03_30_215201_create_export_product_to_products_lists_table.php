<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExportProductToProductsListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('export_product_to_products_lists', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('products_list_id')->index();
            $table->foreign('products_list_id')->references('id')
                ->on('export_products_lists')->onDelete('cascade');

            $table->unsignedBigInteger('product_id')->index();
            $table->foreign('product_id')->references('id')
                ->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('export_product_to_products_lists');
    }
}
