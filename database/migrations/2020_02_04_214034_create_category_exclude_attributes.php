<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryExcludeAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_exclude_attributes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('category_id')->index();
            $table->unsignedBigInteger('attribute_id')->index();

            $table->foreign('category_id')->references('id')
                ->on('categories')
                ->onDelete('cascade');

            $table->foreign('attribute_id')->references('id')
                ->on('attributes')
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
        Schema::dropIfExists('category_exclude_attributes');
    }
}
