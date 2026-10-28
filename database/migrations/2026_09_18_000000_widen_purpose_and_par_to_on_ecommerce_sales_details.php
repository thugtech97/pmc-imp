<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Room for a real purpose on an MRS line.
 *
 * `purpose` and `par_to` were added as plain strings, which SQL Server made
 * nvarchar(191). A requestor describing why a gas detector is needed
 * underground easily passes that, and the whole checkout then fails with
 * "String or binary data would be truncated" — after the sales header has
 * already been written. Both are free text the requestor types, so neither
 * has a sensible fixed limit.
 *
 * Raw ALTERs rather than ->change(): Doctrine turns text() into varchar(max),
 * and par_to carries names with Ñ that must stay nvarchar.
 */
class WidenPurposeAndParToOnEcommerceSalesDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE ecommerce_sales_details ALTER COLUMN purpose NVARCHAR(MAX) NULL');
        DB::statement('ALTER TABLE ecommerce_sales_details ALTER COLUMN par_to NVARCHAR(MAX) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE ecommerce_sales_details ALTER COLUMN purpose NVARCHAR(191) NULL');
        DB::statement('ALTER TABLE ecommerce_sales_details ALTER COLUMN par_to NVARCHAR(191) NULL');
    }
}
