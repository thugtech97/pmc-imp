<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalColumnsToMrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_sales_headers', function (Blueprint $table) {
            $table->string('priority')->nullable();
            $table->string('section')->nullable();
            $table->decimal('budgeted_amount', 16, 4)->nullable();
            $table->decimal('adjusted_amount', 16, 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_sales_headers', function (Blueprint $table) {
            $table->dropColumn('priority');
            $table->dropColumn('section');
            $table->dropColumn('budgeted_amount');
            $table->dropColumn('adjusted_amount');
        });
    }
}
