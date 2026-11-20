<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The IMF No., IMP-IMF- + YYYYMMDD + '-' + 4-digit daily series (IMP-IMF-20261120-0001),
 * given when the IMF is saved — as the MRS gets its order_number — and sent to
 * WFS as the transid. Null for IMFs saved before this; those keep showing
 * their id and their random 'IMP-IMF-' . uniqid() WFS transid.
 *
 * Unique among the non-null values: two IMFs must never be handed the same
 * number. SQL Server's plain unique index allows only one NULL, hence the
 * filtered index.
 */
class AddImfNumberToInventoryRequests extends Migration
{
    public function up()
    {
        Schema::table('inventory_requests', function (Blueprint $table) {
            $table->string('imf_number', 50)->nullable();
        });

        if (DB::getDriverName() === 'sqlsrv') {
            DB::statement('CREATE UNIQUE INDEX inventory_requests_imf_number_unique ON inventory_requests (imf_number) WHERE imf_number IS NOT NULL');
        } else {
            Schema::table('inventory_requests', function (Blueprint $table) {
                $table->unique('imf_number');
            });
        }
    }

    public function down()
    {
        Schema::table('inventory_requests', function (Blueprint $table) {
            $table->dropUnique('inventory_requests_imf_number_unique');
            $table->dropColumn('imf_number');
        });
    }
}
