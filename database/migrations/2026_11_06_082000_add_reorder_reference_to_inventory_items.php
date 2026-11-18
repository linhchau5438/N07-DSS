<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transaction_items', function (Blueprint $table) {
            $table->foreignId('reorder_recommendation_id')
                ->nullable()
                ->after('product_id')
                ->constrained('reorder_recommendations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transaction_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reorder_recommendation_id');
        });
    }
};
