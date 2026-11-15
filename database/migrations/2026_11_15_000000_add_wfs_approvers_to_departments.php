<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WFS approvers are set per department in Admin > Departments instead of being
 * looked up in WFS users by department name. The ids are WFS users.id values.
 *
 *   1st approver (WFS template step 0, MANAGER)          — MRS and IMF
 *   2nd approver (WFS template step 1, DIVISION MANAGER) — MRS only
 */
class AddWfsApproversToDepartments extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedInteger('wfs_approver_id')->nullable();
            $table->unsignedInteger('wfs_alternate_approver_id')->nullable();
            $table->unsignedInteger('wfs_approver2_id')->nullable();
            $table->unsignedInteger('wfs_alternate_approver2_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn([
                'wfs_approver_id',
                'wfs_alternate_approver_id',
                'wfs_approver2_id',
                'wfs_alternate_approver2_id',
            ]);
        });
    }
}
