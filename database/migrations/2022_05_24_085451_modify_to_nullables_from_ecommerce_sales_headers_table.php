<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyToNullablesFromEcommerceSalesHeadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_sales_headers', function (Blueprint $table) {
            $table->text('customer_address')->nullable()->change();
            $table->text('customer_name')->nullable()->change();
            $table->text('customer_email')->nullable()->change();
            $table->text('customer_contact_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_sales_headers', function (Blueprint $table) {
            $table->text('customer_address')->change();
            $table->text('customer_name')->change();
            $table->text('customer_email')->change();
            $table->text('customer_contact_number')->change();
        });
    }
}
