<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reorder_settings', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->primary()
                ->constrained('products')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('lead_time_days')->default(7);
            $table->unsignedSmallInteger('target_days')->default(30);
            $table->decimal('safety_factor', 4, 2)->default(1.65);
            $table->timestamps();
        });

        Schema::create('reorder_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();
            $table->foreignId('proposed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('average_daily_demand', 15, 4)->default(0);
            $table->unsignedInteger('safety_stock')->default(0);
            $table->unsignedInteger('reorder_point')->default(0);
            $table->unsignedInteger('on_hand')->default(0);
            $table->unsignedInteger('on_order')->default(0);
            $table->unsignedInteger('committed')->default(0);
            $table->integer('inventory_position')->default(0);
            $table->unsignedInteger('suggested_quantity')->default(0);
            $table->unsignedInteger('approved_quantity')->nullable();
            $table->unsignedInteger('received_quantity')->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('explanation');
            $table->text('decision_note')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['period_end', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reorder_recommendations');
        Schema::dropIfExists('product_reorder_settings');
    }
};
