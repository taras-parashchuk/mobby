<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCascadeDeleteToSetNullForCategoriesExternalSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories_external_source', function (Blueprint $table) {
            $table->dropForeign('categories_external_source_category_id_foreign');
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories_external_source', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
        Schema::table('categories_external_source', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }
}
