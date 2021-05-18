<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderTotals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_totals', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('order_id')->index();
            $table->string('code', 100)->index()->nullable();

            $table->string('name', 200);
            $table->decimal('value', 8, 2);
            $table->smallInteger('sort_order')->nullable()->default(0);

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
        Schema::dropIfExists('order_totals');
    }
}
