<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_translations', function (Blueprint $table) {

            $table->unsignedBigInteger('article_id')->index();
            $table->string('locale', 5)->index();

            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('meta_title', 250)->nullable();
            $table->string('meta_description', 250)->nullable();
            $table->string('meta_keywords', 250)->nullable();

            $table->foreign('article_id')->references('id')
                ->on('articles')
                ->onDelete('cascade');

            $table->foreign('locale')->references('locale')
                ->on('languages')
                ->onDelete('cascade');

            $table->unique(['article_id', 'locale']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_translations');
    }
}
