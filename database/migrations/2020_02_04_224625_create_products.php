<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('sku', 100)->nullable();
            $table->float('quantity');
            $table->unsignedBigInteger('stock_status_id')->index()->nullable();
            $table->unsignedBigInteger('price_unit_id')->index()->nullable();

            $table->string('image', 200)->nullable();
            $table->timestamp('date_available')->useCurrent();

            $table->float('minimum')->default(1);
            $table->float('multiplicity')->default(1);
            $table->smallInteger('sort_order')->nullable()->default(0);
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('viewed');
            $table->string('slug', 200)->index();
            $table->unsignedBigInteger('category_id')->index()->nullable();
            $table->string('currency_code', 10)->index()->nullable();
            $table->double('vendor_price')->nullable()->default(0);
            $table->smallInteger('type')->default(1);
            $table->unsignedBigInteger('main_id')->index()->nullable();
            $table->unsignedBigInteger('primary_variant_id')->index()->nullable();

            $table->string('extra_1', 240)->nullable();
            $table->string('extra_2', 240)->nullable();

            $table->foreign('stock_status_id')->references('id')
                ->on('stock_statuses')
                ->onDelete('set null');

            $table->foreign('price_unit_id')->references('id')
                ->on('price_units')
                ->onDelete('set null');

            $table->foreign('category_id')->references('id')
                ->on('categories')
                ->onDelete('set null');

            $table->foreign('currency_code')->references('code')
                ->on('currencies')
                ->onDelete('set null');

            $table->foreign('main_id')->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('primary_variant_id')->references('id')
                ->on('products')
                ->onDelete('set null');


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
        Schema::dropIfExists('products');
    }
}
