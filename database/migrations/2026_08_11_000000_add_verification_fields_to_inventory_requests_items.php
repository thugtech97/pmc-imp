<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per line item fields for the MCD Verifier stage of an IMF.
 *
 * The MCD Verifier fills the inventory code, class and DLT of each item before
 * the MCD Planner generates the stock code in Classic and types it back here.
 * Both roles can leave a remark against a single line rather than the whole IMF.
 */
class AddVerificationFieldsToInventoryRequestsItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_requests_items', function (Blueprint $table) {
            // "class" is avoided as a column name so $item->class stays readable.
            $table->string('inventory_code')->nullable()->after('stock_code');
            $table->string('item_class')->nullable()->after('inventory_code');
            $table->string('dlt')->nullable()->after('item_class');
            $table->text('planner_remarks')->nullable()->after('purpose');
            $table->text('verifier_remarks')->nullable()->after('planner_remarks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_requests_items', function (Blueprint $table) {
            $table->dropColumn([
                'inventory_code',
                'item_class',
                'dlt',
                'planner_remarks',
                'verifier_remarks',
            ]);
        });
    }
}
