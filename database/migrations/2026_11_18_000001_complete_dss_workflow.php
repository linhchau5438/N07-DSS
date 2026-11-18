<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->boolean('is_active')->default(true));
        Schema::table('inventory_transactions', function (Blueprint $t) {
            // Legacy exports have no verified purpose: exclude them from demand until reviewed.
            $t->string('issue_purpose', 20)->default('unknown');
            $t->string('reference_no', 100)->nullable();
        });
        Schema::table('product_reorder_settings', function (Blueprint $t) {
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->unsignedSmallInteger('ma_days')->default(30);
            $t->string('safety_mode', 20)->default('statistical');
            $t->unsignedInteger('manual_safety_stock')->default(0);
            $t->unsignedInteger('pack_size')->default(1);
            $t->date('observation_start')->nullable();
            $t->string('forecast_method', 20)->default('ma');
        });
        Schema::table('reorder_recommendations', function (Blueprint $t) {
            $t->string('stock_status', 30)->default('NOT_CALCULATED');
            $t->json('calculation_snapshot')->nullable();
            $t->timestamp('reviewed_at')->nullable();
        });
        Schema::create('forecast_runs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->date('period_start');
            $t->date('period_end');
            $t->unsignedSmallInteger('ma_days');
            $t->string('model_version')->default('linear-trend-v1');
            $t->json('results');
            $t->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('action');
            $t->string('entity_type');
            $t->unsignedBigInteger('entity_id');
            $t->json('before_data')->nullable();
            $t->json('after_data')->nullable();
            $t->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('forecast_runs');
        Schema::table('reorder_recommendations', fn (Blueprint $t) => $t->dropColumn(['stock_status', 'calculation_snapshot', 'reviewed_at']));
        Schema::table('product_reorder_settings', function (Blueprint $t) {
            $t->dropConstrainedForeignId('updated_by');
            $t->dropColumn(['ma_days', 'safety_mode', 'manual_safety_stock', 'pack_size', 'observation_start', 'forecast_method']);
        });
        Schema::table('inventory_transactions', fn (Blueprint $t) => $t->dropColumn(['issue_purpose', 'reference_no']));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_active'));
    }
};
