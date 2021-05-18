<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBannerSlides extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banner_slides', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('banner_id')->index();
            $table->string('locale', 5)->index();

            $table->string('title', 200)->nullable();
            $table->string('link', 200)->nullable();
            $table->string('image', 240);
            $table->smallInteger('sort_order')->nullable()->default(0);

            $table->foreign('banner_id')->references('id')
                ->on('banners')
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
        Schema::dropIfExists('banner_slides');
    }
}
