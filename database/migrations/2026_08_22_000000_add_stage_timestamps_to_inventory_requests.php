<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A dated signature for every desk an IMF passes through.
 *
 * The printed IMF names who acted at each stage but could not say when: the
 * two MCD Planner passes had no stamp at all, and the single approved_at
 * column was written twice — first by the WFS department-head approval, then
 * overwritten by the Planning Supervisor's, so the department head's date was
 * lost the moment the IMF was fully approved.
 *
 * Each stage now keeps its own stamp. approved_at is left in place so the
 * existing queue screens keep working; the new columns are what the printout
 * and the timeline read.
 *
 * submitted_at and approved_at were also DATE columns, so they silently threw
 * away the time of day. They are widened to DATETIME — a widening SQL Server
 * does in place, and existing rows simply keep midnight as their time, which
 * is all that was ever recorded for them.
 */
class AddStageTimestampsToInventoryRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_requests', function (Blueprint $table) {
            // Department head, via WFS.
            $table->dateTime('wfs_approved_at')->nullable()->after('approved_at');
            // MCD Planner, first pass (review, endorsed to the MCD Verifier).
            $table->dateTime('planner_reviewed_at')->nullable()->after('wfs_approved_at');
            // MCD Planner, second pass (stock code entered from Classic).
            $table->dateTime('planner_stock_at')->nullable()->after('planner_reviewed_at');
            // Planning Supervisor, final approval.
            $table->dateTime('supervisor_approved_at')->nullable()->after('planner_stock_at');
        });

        // Doctrine's ->change() is not available here, and the widening is a
        // one-liner SQL Server applies without rewriting the rows.
        if (DB::connection()->getDriverName() === 'sqlsrv') {
            DB::statement('ALTER TABLE inventory_requests ALTER COLUMN submitted_at datetime NULL');
            DB::statement('ALTER TABLE inventory_requests ALTER COLUMN approved_at datetime NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::connection()->getDriverName() === 'sqlsrv') {
            // Narrowing back to DATE discards the time of day by design.
            DB::statement('ALTER TABLE inventory_requests ALTER COLUMN submitted_at date NULL');
            DB::statement('ALTER TABLE inventory_requests ALTER COLUMN approved_at date NULL');
        }

        Schema::table('inventory_requests', function (Blueprint $table) {
            $table->dropColumn([
                'wfs_approved_at',
                'planner_reviewed_at',
                'planner_stock_at',
                'supervisor_approved_at',
            ]);
        });
    }
}
