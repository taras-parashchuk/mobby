<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockStatusTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_status_translations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('stock_status_id')->index();
            $table->string('locale', 5)->index();

            $table->string('title', 100);

            $table->foreign('locale')->references('locale')
                ->on('languages')
                ->onDelete('cascade');

            $table->foreign('stock_status_id')->references('id')
                ->on('stock_statuses')
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
        Schema::dropIfExists('stock_status_translations');
    }
}
