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
            $table->string('department');
            $table->string('section')->nullable();
            $table->string('division')->nullable();
            $table->enum('status', ['SAVED', 'SUBMITTED', 'RECEIVED', 'ON HOLD', 'APPROVED'])->default('SAVED');
            $table->string('attachments')->nullable();
            $table->string('type');
            $table->string('approved_by')->nullable();
            $table->date('submitted_at')->nullable();
            $table->date('approved_at')->nullable();
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
