<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_code', 30)->unique();
                $table->string('type', 20);
                $table->date('transaction_date');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('total_value', 15, 2)->default(0);
                $table->text('note')->nullable();
                $table->timestamps();

                $table->index(['type', 'transaction_date']);
            });
        }

        if (! Schema::hasTable('inventory_transaction_items')) {
            Schema::create('inventory_transaction_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_transaction_id')
                    ->constrained('inventory_transactions')
                    ->cascadeOnDelete();
                $table->foreignId('product_id')
                    ->constrained('products')
                    ->restrictOnDelete();
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 15, 2);
                $table->decimal('line_total', 15, 2);
                $table->timestamps();

                $table->unique(
                    ['inventory_transaction_id', 'product_id'],
                    'inv_tx_items_order_product_unique',
                );
                $table->index('product_id', 'inv_tx_items_product_index');
            });

            return;
        }

        if (! Schema::hasIndex('inventory_transaction_items', 'inv_tx_items_order_product_unique')) {
            Schema::table('inventory_transaction_items', function (Blueprint $table) {
                $table->unique(
                    ['inventory_transaction_id', 'product_id'],
                    'inv_tx_items_order_product_unique',
                );
            });
        }

        if (! Schema::hasIndex('inventory_transaction_items', 'inv_tx_items_product_index')) {
            Schema::table('inventory_transaction_items', function (Blueprint $table) {
                $table->index('product_id', 'inv_tx_items_product_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transaction_items');
        Schema::dropIfExists('inventory_transactions');
    }
};
