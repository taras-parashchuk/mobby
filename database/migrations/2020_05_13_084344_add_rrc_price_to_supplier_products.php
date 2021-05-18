<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRrcPriceToSupplierProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            //
            $table->decimal('rrc_price', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            //
            $table->dropColumn('rrc_price');
        });
    }
}
