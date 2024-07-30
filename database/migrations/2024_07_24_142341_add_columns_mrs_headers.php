<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsMrsHeaders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_sales_headers', function (Blueprint $table) {
            $table->string('requested_by')->nullable();
            $table->string('note_planner')->nullable();
            $table->string('note_verifier')->nullable();
            $table->string('note_myrna')->nullable();
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
            $table->dropColumn('requested_by');
            $table->dropColumn('note_planner');
            $table->dropColumn('note_verifier');
            $table->dropColumn('note_myrna');
            $table->string('received_by')->nullable();
            $table->dateTime('received_at')->nullable();
        });
    }
}
