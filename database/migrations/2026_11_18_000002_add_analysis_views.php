<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reorder_recommendations', fn (Blueprint $t) => $t->decimal('reorder_point', 18, 4)->default(0)->change());
        DB::statement("CREATE VIEW v_daily_demand AS SELECT i.product_id, t.transaction_date AS demand_date,
            SUM(i.quantity) AS consumed_qty FROM inventory_transaction_items i
            JOIN inventory_transactions t ON t.id = i.inventory_transaction_id
            WHERE t.type = 'export' AND t.issue_purpose = 'sale' GROUP BY i.product_id, t.transaction_date");
        DB::statement("CREATE VIEW v_stock_balances AS SELECT p.id AS product_id,
            COALESCE(SUM(CASE WHEN t.type = 'import' THEN i.quantity WHEN t.type = 'export' THEN -i.quantity ELSE 0 END), 0) AS on_hand
            FROM products p LEFT JOIN inventory_transaction_items i ON p.id = i.product_id
            LEFT JOIN inventory_transactions t ON t.id = i.inventory_transaction_id GROUP BY p.id");
        DB::statement('CREATE VIEW v_reorder AS SELECT r.* FROM reorder_recommendations r
            JOIN (SELECT product_id, MAX(id) AS latest_id FROM reorder_recommendations GROUP BY product_id) latest ON r.id = latest.latest_id');
        DB::statement('CREATE VIEW v_inventory_facts AS SELECT i.id, i.product_id, p.category_id, p.unit,
            t.transaction_date, t.type, t.issue_purpose, i.quantity, i.line_total FROM inventory_transaction_items i
            JOIN inventory_transactions t ON t.id = i.inventory_transaction_id JOIN products p ON p.id = i.product_id');
    }

    public function down(): void
    {
        foreach (['v_inventory_facts', 'v_reorder', 'v_stock_balances', 'v_daily_demand'] as $view) {
            DB::statement('DROP VIEW IF EXISTS '.$view);
        }
        Schema::table('reorder_recommendations', fn (Blueprint $t) => $t->unsignedInteger('reorder_point')->default(0)->change());
    }
};
