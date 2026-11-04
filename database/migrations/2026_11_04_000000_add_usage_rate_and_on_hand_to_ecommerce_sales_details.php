<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-line UR and SOH on a DP-MRS, typed by the MCD Planner.
 *
 * The DP Purchase Advice used to print the product master's usage_rate_qty /
 * on_hand, which are mostly blank for DP items, so the PA showed 0/0. The SR-PA
 * already keeps its own per-line copy on purchase_advice_details; this gives
 * the DP side the same. Null = never entered, the print falls back to the
 * product master. (open_po already exists on this table.)
 */
class AddUsageRateAndOnHandToEcommerceSalesDetails extends Migration
{
    public function up()
    {
        Schema::table('ecommerce_sales_details', function (Blueprint $table) {
            $table->decimal('usage_rate_qty', 12, 2)->nullable();
            $table->decimal('on_hand', 12, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('ecommerce_sales_details', function (Blueprint $table) {
            $table->dropColumn(['usage_rate_qty', 'on_hand']);
        });
    }
}
