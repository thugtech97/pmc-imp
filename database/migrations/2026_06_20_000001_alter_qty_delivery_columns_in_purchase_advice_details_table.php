<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterQtyDeliveryColumnsInPurchaseAdviceDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_advice_details', function (Blueprint $table) {
            $table->string('qty_per_delivery', 191)->nullable()->change();
            $table->string('number_of_deliveries', 191)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('purchase_advice_details', function (Blueprint $table) {
            $table->integer('qty_per_delivery')->nullable()->change();
            $table->integer('number_of_deliveries')->nullable()->change();
        });
    }
}
