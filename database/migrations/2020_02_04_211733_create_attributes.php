<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('group_id')->index()->nullable();

            $table->smallInteger('sort_order')->nullable()->default(0);
            $table->boolean('status')->default(true);
            $table->string('slug', 120)->index();

            $table->foreign('group_id')->references('id')
                ->on('attribute_groups')
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
        Schema::dropIfExists('attributes');
    }
}
