<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Signatory and remark columns for the MCD Verifier stage.
 *
 * note_verifier / approver_approved_by already existed but belong to the final
 * approver (the Planning Supervisor), so the MCD Verifier gets its own pair
 * instead of sharing them.
 */
class AddMcdVerifierColumnsToInventoryRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_requests', function (Blueprint $table) {
            $table->text('note_mcd_verifier')->nullable()->after('note_verifier');
            $table->string('verifier_approved_by')->nullable()->after('planner_approved_by');
            $table->dateTime('verified_at')->nullable()->after('approved_at');
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
            $table->dropColumn(['note_mcd_verifier', 'verifier_approved_by', 'verified_at']);
        });
    }
}
