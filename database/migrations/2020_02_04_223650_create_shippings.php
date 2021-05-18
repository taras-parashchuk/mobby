<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shippings', function (Blueprint $table) {
            $table->bigIncrements('id');


            $table->string('code', 20);
            $table->longText('name');
            $table->text('payments')->nullable();
            $table->text('fields')->nullable();
            $table->boolean('has_api')->default(false);
            $table->text('settings');
            $table->smallInteger('sort_order')->nullable()->default(0);
            $table->boolean('status')->default(true);
            $table->string('decode_address_fields', 220);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shippings');
    }
}
