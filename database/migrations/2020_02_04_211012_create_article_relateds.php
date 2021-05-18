<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleRelateds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_relateds', function (Blueprint $table) {

            $table->bigIncrements('id');


            $table->unsignedBigInteger('recipient_id')->index();
            $table->unsignedBigInteger('source_id')->index();

            $table->foreign('recipient_id')->references('id')
                ->on('articles')
                ->onDelete('cascade');

            $table->foreign('source_id')->references('id')
                ->on('articles')
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
        Schema::dropIfExists('article_relateds');
    }
}
