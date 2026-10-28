<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToPurchaseAdviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_advice', function (Blueprint $table) {
            $table->text('planner_remarks')->nullable();
            $table->string('status')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->integer('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->integer('received_by')->nullable();
            $table->dateTime('received_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_advice', function (Blueprint $table) {
            //
            $table->dropColumn('status');
            $table->dropColumn('planner_remarks');
            $table->dropColumn('created_by');
            $table->dropColumn('verified_by');
            $table->dropColumn('verified_at');
            $table->dropColumn('approved_by');
            $table->dropColumn('approved_at');
            $table->dropColumn('received_by');
            $table->dropColumn('received_at');
        });
    }
}
