<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMcdApproverNamesToInventoryRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_requests', function (Blueprint $table) {
            $table->string('planner_approved_by')->nullable()->after('note_verifier');
            $table->string('approver_approved_by')->nullable()->after('planner_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_requests', function (Blueprint $table) {
            $table->dropColumn(['planner_approved_by', 'approver_approved_by']);
        });
    }
}
