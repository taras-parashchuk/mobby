<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTestimonials extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('product_id')->index()->nullable();
            $table->unsignedBigInteger('user_id')->index()->nullable();

            $table->string('name', 100);
            $table->text('text');
            $table->smallInteger('rating')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->boolean('status')->default(false);
            $table->unsignedInteger('_lft')->nullable();
            $table->unsignedInteger('_rgt')->nullable();

            $table->string('email', 120);


            $table->foreign('parent_id')->references('id')
                ->on('testimonials')
                ->onDelete('cascade');

            $table->foreign('product_id')->references('id')
                ->on('products')
                ->onDelete('set null');

            $table->foreign('user_id')->references('id')
                ->on('users')
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
        Schema::dropIfExists('testimonials');
    }
}
