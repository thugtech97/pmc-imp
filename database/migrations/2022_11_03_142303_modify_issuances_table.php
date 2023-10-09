<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyIssuancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('issuances', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('issuances', function (Blueprint $table) {
            $table->smallInteger('quantity')->nullable();
            $table->date('release_date')->default(date('Y-m-d'));
            $table->string('received_by')->nullable();
            $table->string('issued_by')->nullable();
        });
    }
}
