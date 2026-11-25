<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tạm thời drop các Views trước khi alter bảng (tương thích cả SQLite in-memory và MySQL)
        foreach (['v_inventory_facts', 'v_reorder', 'v_stock_balances', 'v_daily_demand'] as $view) {
            DB::statement('DROP VIEW IF EXISTS '.$view);
        }

        // 1. Cho phép selling_price ở bảng products là nullable (không bắt buộc giá bán trong kho nội bộ)
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('selling_price', 15, 2)->nullable()->change();
        });

        // 2. Cho phép unit_price và line_total trên chi tiết phiếu xuất là nullable
        Schema::table('inventory_transaction_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->nullable()->change();
            $table->decimal('line_total', 15, 2)->nullable()->change();
        });

        // 3. Mở rộng bảng reorder_recommendations để quản lý đặt hàng thực tế và hủy đơn
        Schema::table('reorder_recommendations', function (Blueprint $table) {
            $table->unsignedInteger('ordered_quantity')->nullable()->after('approved_quantity');
            $table->foreignId('ordered_by')->nullable()->after('reviewed_by')->constrained('users')->nullOnDelete();
            $table->timestamp('ordered_at')->nullable()->after('reviewed_at');
            $table->date('expected_delivery_date')->nullable()->after('ordered_at');
            $table->string('supplier_name', 150)->nullable()->after('expected_delivery_date');
            $table->text('order_note')->nullable()->after('supplier_name');
            $table->timestamp('cancelled_at')->nullable()->after('order_note');
            $table->foreignId('cancelled_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
        });

        // 4. Bảo toàn và chuyển đổi dữ liệu lịch sử một cách an toàn
        // - Các đơn đã nhận đủ hàng trong quá khứ -> fulfilled
        DB::table('reorder_recommendations')
            ->whereNotNull('approved_quantity')
            ->where('approved_quantity', '>', 0)
            ->whereRaw('received_quantity >= approved_quantity')
            ->update([
                'ordered_quantity' => DB::raw('approved_quantity'),
                'status' => 'fulfilled',
            ]);

        // - Các đơn đã nhận một phần trong quá khứ -> partially_received (có bằng chứng đã đặt NCC)
        DB::table('reorder_recommendations')
            ->whereNotNull('approved_quantity')
            ->where('received_quantity', '>', 0)
            ->whereRaw('received_quantity < approved_quantity')
            ->update([
                'ordered_quantity' => DB::raw('approved_quantity'),
                'status' => 'partially_received',
            ]);

        // 5. Tái tạo lại 4 Views SQL với định nghĩa chuẩn hóa hỗ trợ cả consumption và sale
        DB::statement("CREATE VIEW v_daily_demand AS SELECT i.product_id, t.transaction_date AS demand_date,
            SUM(i.quantity) AS consumed_qty FROM inventory_transaction_items i
            JOIN inventory_transactions t ON t.id = i.inventory_transaction_id
            WHERE t.type = 'export' AND t.issue_purpose IN ('sale', 'consumption')
            GROUP BY i.product_id, t.transaction_date");

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

        Schema::table('reorder_recommendations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ordered_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn([
                'ordered_quantity',
                'ordered_at',
                'expected_delivery_date',
                'supplier_name',
                'order_note',
                'cancelled_at',
                'cancellation_reason',
            ]);
        });

        Schema::table('inventory_transaction_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->nullable(false)->change();
            $table->decimal('line_total', 15, 2)->nullable(false)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('selling_price', 15, 2)->nullable(false)->change();
        });

        DB::statement("CREATE VIEW v_daily_demand AS SELECT i.product_id, t.transaction_date AS demand_date,
            SUM(i.quantity) AS consumed_qty FROM inventory_transaction_items i
            JOIN inventory_transactions t ON t.id = i.inventory_transaction_id
            WHERE t.type = 'export' AND t.issue_purpose = 'sale'
            GROUP BY i.product_id, t.transaction_date");

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
};
