<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An Update IMF can now carry several items, so each item keeps its own
 * "before" snapshot. Rows written before this change have no item_id: those
 * IMFs only ever had one item, and they are still matched to it by imf_no.
 */
class AddItemIdToInventoryRequestsOldItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_requests_old_items', function (Blueprint $table) {
            $table->bigInteger('item_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_requests_old_items', function (Blueprint $table) {
            $table->dropColumn('item_id');
        });
    }
}
