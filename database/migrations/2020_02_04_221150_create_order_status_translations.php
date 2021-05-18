<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderStatusTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_status_translations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('order_status_id')->index();
            $table->string('locale', 5)->index();

            $table->string('name', 200);

            $table->foreign('order_status_id')->references('id')
                ->on('order_statuses')
                ->onDelete('cascade');

            $table->foreign('locale')->references('locale')
                ->on('languages')
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
        Schema::dropIfExists('order_status_translations');
    }
}
