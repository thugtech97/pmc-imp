<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchaserRemarksToPurchaseAdvice extends Migration
{
    public function up()
    {
        Schema::table('purchase_advice', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_advice', 'purchaser_remarks')) {
                $table->text('purchaser_remarks')->nullable()->after('approver_remarks');
            }
        });
    }

    public function down()
    {
        Schema::table('purchase_advice', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_advice', 'purchaser_remarks')) {
                $table->dropColumn('purchaser_remarks');
            }
        });
    }
}
