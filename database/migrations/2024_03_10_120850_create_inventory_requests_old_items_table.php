<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryRequestsOldItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_requests_old_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('imf_no')->nullable();
            $table->string('stock_code')->nullable();
            $table->text('item_description')->nullable();
            $table->string('brand')->nullable();
            $table->string('OEM_ID')->nullable();
            $table->string('UoM')->nullable();
            $table->integer('usage_rate_qty')->nullable();
            $table->string('usage_frequency')->nullable();
            $table->string('purpose')->nullable();
            $table->integer('min_qty')->nullable();
            $table->integer('max_qty')->nullable();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_requests_old_items');
    }
}
