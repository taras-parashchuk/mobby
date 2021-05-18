<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeToSupplierCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_to_supplier_categories', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('attribute_id')->index();
            $table->unsignedBigInteger('category_id')->index();

            $table->foreign('attribute_id')->references('id')->on('attributes')
                ->onDelete('cascade');

            $table->foreign('category_id')->references('id')->on('supplier_categories')
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
        Schema::dropIfExists('attribute_to_supplier_categories');
    }
}
