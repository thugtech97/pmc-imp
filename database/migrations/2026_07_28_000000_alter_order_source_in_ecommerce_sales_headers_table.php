<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterOrderSourceInEcommerceSalesHeadersTable extends Migration
{
    public function up()
    {
        // order_source keeps every MRS attachment path joined by "|", so nvarchar(250)
        // overflows once a request has more than 2-3 files.
        $this->dropDefaultConstraint();

        DB::statement('ALTER TABLE [ecommerce_sales_headers] ALTER COLUMN [order_source] NVARCHAR(MAX) NULL');
    }

    public function down()
    {
        $this->dropDefaultConstraint();

        DB::statement('UPDATE [ecommerce_sales_headers] SET [order_source] = LEFT([order_source], 250) WHERE LEN([order_source]) > 250');
        DB::statement('ALTER TABLE [ecommerce_sales_headers] ALTER COLUMN [order_source] NVARCHAR(250) NULL');
    }

    /**
     * SQL Server blocks ALTER COLUMN while an (auto-named) default constraint
     * references the column. The generated name differs per environment, so
     * look it up instead of hard-coding it.
     */
    private function dropDefaultConstraint()
    {
        $constraint = DB::selectOne("
            SELECT dc.name
            FROM sys.default_constraints dc
            JOIN sys.columns c
              ON c.object_id = dc.parent_object_id
             AND c.column_id = dc.parent_column_id
            WHERE dc.parent_object_id = OBJECT_ID('ecommerce_sales_headers')
              AND c.name = 'order_source'
        ");

        if ($constraint) {
            DB::statement('ALTER TABLE [ecommerce_sales_headers] DROP CONSTRAINT [' . $constraint->name . ']');
        }
    }
}
