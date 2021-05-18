<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImageColorToAttributeValueToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attribute_value_to_products', function (Blueprint $table) {
            //
            $table->string('image', 200)->nullable();
            $table->string('color', 9)->default('');
            $table->string('prefer_style', 5)->nullable()->default(null);;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attribute_value_to_products', function (Blueprint $table) {
            //
            $table->dropColumn('image');
            $table->dropColumn('color');
            $table->dropColumn('prefer_style');
        });
    }
}
