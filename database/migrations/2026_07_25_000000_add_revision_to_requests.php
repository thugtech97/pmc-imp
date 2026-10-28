<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRevisionToRequests extends Migration
{
    /**
     * Revision counter for IMF / MRS / PA. Starts at 0 (no "Rev" shown);
     * incremented each time a held request is re-edited by a user.
     *
     * @return void
     */
    public function up()
    {
        foreach (['inventory_requests', 'ecommerce_sales_headers', 'purchase_advice'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'revision')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedInteger('revision')->default(0);
                });
            }
        }
    }

    /**
     * @return void
     */
    public function down()
    {
        foreach (['inventory_requests', 'ecommerce_sales_headers', 'purchase_advice'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'revision')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('revision');
                });
            }
        }
    }
}
