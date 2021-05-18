<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributeValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('attribute_id')->index();

            $table->string('image', 200);
            $table->string('slug', 120)->index();
            $table->smallInteger('sort_order')->nullable()->default(0);
            $table->boolean('status')->default(true);


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
        Schema::dropIfExists('attribute_values');
    }
}
