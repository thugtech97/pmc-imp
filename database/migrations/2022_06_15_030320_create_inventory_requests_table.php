<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_requests', function (Blueprint $table) {
            $table->id();
            $table->string('stock_code');
            $table->string('department');
            $table->string('section')->nullable();
            $table->string('division')->nullable();
            $table->text('item_description');
            $table->string('brand')->nullable();
            $table->string('OEM_ID')->nullable();
            $table->string('UoM')->nullable();
            $table->integer('usage_rate_qty')->nullable();
            $table->string('usage_frequency')->nullable();
            $table->string('purpose')->nullable();
            $table->integer('min_qty')->default(1);
            $table->integer('max_qty')->nullable();
            $table->enum('status', ['SAVED', 'SUBMITTED', 'RECEIVED', 'ON HOLD', 'APPROVED'])->default('SAVED');
            $table->string('attachments')->nullable();
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
        Schema::dropIfExists('inventory_requests');
    }
}
