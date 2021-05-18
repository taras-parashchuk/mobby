<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_translations', function (Blueprint $table) {

            $table->unsignedBigInteger('user_group_id')->index();
            $table->string('locale', 5)->index();

            $table->string('name', 100);
            $table->string('summary', 220)->nullable();

            $table->foreign('user_group_id')->references('id')
                ->on('user_groups')
                ->onDelete('cascade');

            $table->foreign('locale')->references('locale')
                ->on('languages')
                ->onDelete('cascade');

            $table->unique(['locale', 'user_group_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_translations');
    }
}
