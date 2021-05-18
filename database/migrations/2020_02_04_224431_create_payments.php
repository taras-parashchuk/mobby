<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code', 20);
            $table->longText('name');
            $table->text('shippings')->nullable();
            $table->text('fields')->nullable();
            $table->boolean('has_api')->default(false);
            $table->text('settings')->nullable();
            $table->smallInteger('sort_order')->nullable()->default(0);
            $table->boolean('status')->default(true);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
