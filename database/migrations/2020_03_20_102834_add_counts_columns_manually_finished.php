<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCountsColumnsManuallyFinished extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('syncs', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('success_products_count')->nullable();
            $table->unsignedBigInteger('success_categories_count')->nullable();
            $table->unsignedBigInteger('warnings_count')->nullable();

            $table->dropColumn('configuration');
            $table->unsignedBigInteger('configuration_id')->index()->nullable();

            $table->boolean('finished')->default(false);
            $table->boolean('manually')->default(true);

            $table->boolean('fatal_error')->default(false);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('syncs', function (Blueprint $table) {
            //

            $table->dropColumn('success_products_count');
            $table->dropColumn('success_categories_count');
            $table->dropColumn('warnings_count');

            $table->dropColumn('configuration_id');
            $table->text('configuration');

            $table->dropColumn('finished');
            $table->dropColumn('manually');
            $table->dropColumn('fatal_error');

        });
    }
}
