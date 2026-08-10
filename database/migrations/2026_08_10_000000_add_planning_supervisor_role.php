<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the Planning Supervisor role, which took over the final IMF approval from
 * the MCD Approver. The role id differs per environment, so every check in the
 * app matches on the name rather than the id.
 */
class AddPlanningSupervisorRole extends Migration
{
    const ROLE_NAME = 'Planning Supervisor';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $existing = DB::table('role')->where('name', self::ROLE_NAME)->first();

        if ($existing) {
            // Already present (or previously soft-deleted) — make sure it is active.
            DB::table('role')->where('id', $existing->id)->update([
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('role')->insert([
            'name'        => self::ROLE_NAME,
            'description' => 'Final approver for IMF requests. IMF module only.',
            'created_by'  => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Soft delete so any user still pointing at the role keeps a valid row.
        DB::table('role')->where('name', self::ROLE_NAME)->update([
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
