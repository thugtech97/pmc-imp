<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The MCD Planner's acknowledgement that a stock code is already taken.
 *
 * A "new" IMF whose stock code already sits in the item master is usually one
 * of two things: a typo, or an item that genuinely exists in Classic already
 * and was raised as new by mistake. The first must be corrected; the second is
 * really an update, and the Planner is the one who can tell them apart.
 *
 * Registering it as new anyway is never an option — products.code carries no
 * unique index, so a second row would be inserted silently and the four
 * lookups that resolve a product by its code would then pick between them at
 * random. A line the Planner has acknowledged is therefore applied to the
 * existing product instead, exactly as an "update" IMF would be.
 *
 * The decision is recorded per line so the Planning Supervisor sees who made
 * it and why before signing, rather than being asked to confirm it blind a
 * second time.
 */
class AddStockCodeOverrideToInventoryRequestsItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_requests_items', function (Blueprint $table) {
            // Set only by the MCD Planner, and cleared the moment the line's
            // stock code is edited again — the acknowledgement belongs to the
            // code that was on screen when it was given.
            $table->boolean('stock_code_override')->default(0);
            $table->text('stock_code_override_note')->nullable();
            $table->string('stock_code_override_by')->nullable();
            $table->dateTime('stock_code_override_at')->nullable();
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
                'stock_code_override',
                'stock_code_override_note',
                'stock_code_override_by',
                'stock_code_override_at',
            ]);
        });
    }
}
