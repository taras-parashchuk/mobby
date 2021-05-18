<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedirectSources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redirect_sources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url', 200)->index();

            $table->unsignedBigInteger('target_id')->index();

            $table->foreign('target_id')->references('id')
                ->on('redirect_targets')
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
        Schema::dropIfExists('redirect_sources');
    }
}
