<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceSourceAndOwnPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->string('price_source', 10)->default('Brain')->nullable()->index();

            $table->renameColumn('vendor_price', 'warehouse_price');

            $table->foreign('price_source')->references('supplier_code')
                ->on('supplier_products')
                ->onDelete('set null');

        });

        Schema::table('products', function (Blueprint $table) {
            //
            $table->decimal('vendor_price', 10, 2);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            //
            $table->dropColumn('price_source');

            $table->dropColumn('vendor_price');

            $table->renameColumn('warehouse_price', 'vendor_price');

        });
    }
}
