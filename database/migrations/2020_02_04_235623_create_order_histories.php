<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_histories', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('order_status_id')->index()->nullable();

            $table->boolean('notify')->default(false);
            $table->longText('comment');

            $table->timestamp('date_added')->useCurrent();

            $table->foreign('order_status_id')->references('id')
                ->on('order_statuses')
                ->onDelete('set null');

            $table->foreign('order_id')->references('id')
                ->on('orders')
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
        Schema::dropIfExists('order_histories');
    }
}
