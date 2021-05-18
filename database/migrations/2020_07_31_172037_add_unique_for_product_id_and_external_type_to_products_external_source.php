<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueForProductIdAndExternalTypeToProductsExternalSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_external_source', function (Blueprint $table) {
            $table->unique(['product_id', 'external_type']);
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
            $table->dropUnique(['product_id', 'external_type']);
        });
    }
}
