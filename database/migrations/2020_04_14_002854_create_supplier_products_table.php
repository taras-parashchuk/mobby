<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSupplierProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('supplier_uuid')->index();
            $table->string('supplier_code', 10)->index();

            $table->string('sku', 100)->index();
            $table->string('name', 200);
            $table->unsignedBigInteger('quantity');
            $table->unsignedDecimal('price', 10, 4);
            $table->unsignedBigInteger('product_id')->index()->nullable();
            $table->string('category_id', 200)->index()->nullable();

            $table->foreign('product_id')->references('id')->on('products')
                ->onDelete('set null');
            $table->foreign('category_id')->references('supplier_uuid')->on('supplier_categories')
                ->onDelete('cascade');

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_products');
    }
}
