<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->unsignedBigInteger('user_group_id')->index()->nullable();
            $table->string('currency_code', 10)->index()->nullable();

            $table->string('firstname', 40)->nullable();
            $table->string('surname', 40)->nullable();
            $table->string('lastname', 40)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('locale', 5)->nullable()->index();

            $table->string('telephone', 30);
            $table->longText('address')->nullable();

            $table->string('shipping_code', 20)->index()->nullable();
            $table->string('payment_code', 20)->index()->nullable();

            $table->longText('comment')->nullable();


            $table->string('ip', 45)->nullable();
            $table->string('forwarded_ip', 45)->nullable();
            $table->string('user_agent', 225)->nullable();
            $table->string('accept_language', 225)->nullable();

            $table->string('exchange_rate', 25);;


            $table->foreign('user_id')->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('user_group_id')->references('id')
                ->on('user_groups')
                ->onDelete('set null');

            $table->foreign('currency_code')->references('code')
                ->on('currencies')
                ->onDelete('set null');

            $table->foreign('locale')->references('locale')
                ->on('languages')
                ->onDelete('set null');

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
        Schema::dropIfExists('orders');
    }
}
