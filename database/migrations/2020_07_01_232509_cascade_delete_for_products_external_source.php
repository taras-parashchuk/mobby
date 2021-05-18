<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CascadeDeleteForProductsExternalSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_external_source', function (Blueprint $table) {
            //

            $table->dropForeign(['product_id']);

            $table->foreign('product_id')
                ->references('id')
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
        Schema::table('products_external_source', function (Blueprint $table) {
            //

            $table->dropForeign(['product_id']);

            $table->foreign('product_id')
                ->references('id')
                ->on('products');

        });
    }
}
