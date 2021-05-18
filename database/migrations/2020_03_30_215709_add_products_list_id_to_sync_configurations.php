<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductsListIdToSyncConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sync_configurations', function (Blueprint $table) {
            //
            $table->unsignedInteger('products_list_id')->index()->nullable();

            $table->foreign('products_list_id')->references('id')
                ->on('export_products_lists')
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
        Schema::table('sync_configurations', function (Blueprint $table) {
            //
            $table->dropColumn('products_list_id');
        });
    }
}
