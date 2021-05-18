<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('image', 200);
            $table->unsignedBigInteger('parent_id')->index()->nullable();
            $table->smallInteger('sort_order')->nullable()->default(0);
            $table->boolean('status')->default(true);
            $table->unsignedInteger('_lft')->nullable();
            $table->unsignedInteger('_rgt')->nullable();
            $table->string('slug', 120)->index();

            $table->string('extra_1', 240)->index()->nullable();
            $table->string('extra_2', 240)->index()->nullable();


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
        Schema::dropIfExists('categories');
    }
}
