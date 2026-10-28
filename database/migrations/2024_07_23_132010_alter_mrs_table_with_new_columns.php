<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMrsTableWithNewColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_sales_details', function (Blueprint $table) {
            $table->string('frequency')->nullable();
            $table->string('par_to')->nullable();
            $table->dateTime('date_needed')->nullable();
            $table->string('purpose')->nullable();
            $table->string('previous_mrs')->nullable();
            $table->string('open_po')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_sales_details', function (Blueprint $table) {
            $table->dropColumn('frequency');
            $table->dropColumn('par_to');
            $table->dropColumn('date_needed');
            $table->dropColumn('purpose');
            $table->dropColumn('previous_mrs');
            $table->dropColumn('open_po');
        });
    }
}
