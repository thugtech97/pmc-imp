<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRevisedAtToRequests extends Migration
{
    /**
     * Timestamp of the most recent revision (set whenever `revision` is bumped).
     * Nullable — stays null until a request is first revised.
     *
     * @return void
     */
    public function up()
    {
        foreach (['inventory_requests', 'ecommerce_sales_headers', 'purchase_advice'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'revised_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dateTime('revised_at')->nullable();
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
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'revised_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('revised_at');
                });
            }
        }
    }
}
