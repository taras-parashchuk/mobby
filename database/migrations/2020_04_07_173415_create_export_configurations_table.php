<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExportConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('export_configurations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 60);
            $table->unsignedInteger('products_list_id')->index()->nullable();
            $table->foreign('products_list_id')->references('id')->on('export_products_lists')
                ->onDelete('set null');
            $table->text('settings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('export_configurations');
    }
}
