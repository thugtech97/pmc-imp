<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHoldColumnsToPurchaseAdviceDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_advice_details', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_advice_details', 'is_hold')) {
                $table->boolean('is_hold')->default(0);
            }
            if (!Schema::hasColumn('purchase_advice_details', 'hold_remarks')) {
                $table->text('hold_remarks')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('purchase_advice_details', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_advice_details', 'is_hold')) {
                $table->dropColumn('is_hold');
            }
            if (Schema::hasColumn('purchase_advice_details', 'hold_remarks')) {
                $table->dropColumn('hold_remarks');
            }
        });
    }
}
