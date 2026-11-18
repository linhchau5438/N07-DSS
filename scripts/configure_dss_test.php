<?php

// Explicitly authorized test settings, persisted in the group's configured database.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::create('http://localhost'));
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Services\AuditLog;
use App\Services\ReorderRecommendationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

if (DB::connection()->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() !== 'n07_dss') {
    throw new RuntimeException('Expected group MySQL database n07_dss.');
}
$settings = ['ma_days' => 7, 'lead_time_days' => 3, 'target_days' => 7,
    'safety_mode' => 'manual', 'manual_safety_stock' => 10, 'safety_factor' => 1.65,
    'pack_size' => 1, 'observation_start' => '2026-11-08', 'forecast_method' => 'ma'];
if (! in_array('--apply', $argv, true)) {
    echo json_encode(['database' => 'n07_dss', 'settings' => $settings, 'apply' => false]).PHP_EOL;
    exit(0);
}
$actor = DB::table('users')->where('is_active', 1)->where('role', 'admin')->orderBy('id')->value('id');
if (! $actor) {
    throw new RuntimeException('An active admin is required.');
}
Auth::loginUsingId($actor);
$report = DB::transaction(function () use ($settings, $actor) {
    $products = DB::table('products')->where('status', 1)->orderBy('id')->lockForUpdate()->get();
    $stockBefore = DB::table('v_stock_balances')->orderBy('product_id')->get()->toJson();
    $voucherCount = DB::table('inventory_transactions')->count();
    $snapshot = ['settings' => DB::table('product_reorder_settings')->get(),
        'recommendations' => DB::table('reorder_recommendations')->get(),
        'stock' => json_decode($stockBefore), 'created_at' => now()->toIso8601String()];
    $directory = storage_path('app/backups');
    if (! is_dir($directory)) {
        mkdir($directory, 0700, true);
    }
    file_put_contents($directory.'/.htaccess', "Require all denied\n");
    if (file_put_contents($directory.'/before-dss-test-settings-'.now()->format('YmdHis').'.json', json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) === false) {
        throw new RuntimeException('Could not back up previous settings.');
    }
    foreach ($products as $product) {
        $previous = DB::table('product_reorder_settings')->where('product_id', $product->id)->first();
        $values = $settings + ['updated_by' => $actor, 'updated_at' => now()];
        if (! $previous) {
            $values['created_at'] = now();
        }
        DB::table('product_reorder_settings')->updateOrInsert(['product_id' => $product->id], $values);
        AuditLog::record('CONFIGURE_DSS_TEST', 'products', $product->id, $previous,
            $values + ['reason' => 'User-authorized test assumptions for batch TEST-N07-20261118; not validated supplier terms.']);
    }
    app(ReorderRecommendationService::class)->refreshIfNeeded($actor);
    if ($stockBefore !== DB::table('v_stock_balances')->orderBy('product_id')->get()->toJson()
        || $voucherCount !== DB::table('inventory_transactions')->count()) {
        throw new RuntimeException('Configuration must not change stock or vouchers.');
    }
    $rows = DB::table('v_reorder as r')->join('products as p', 'r.product_id', '=', 'p.id')
        ->where('p.status', 1)->select('p.product_code', 'p.name', 'r.on_hand', 'r.average_daily_demand',
            'r.reorder_point', 'r.inventory_position', 'r.suggested_quantity', 'r.stock_status', 'r.status')->orderBy('p.id')->get();
    if ($rows->count() !== $products->count() || $rows->contains(fn ($r) => in_array($r->stock_status, ['MISSING_CONFIG', 'INSUFFICIENT_DATA', 'INVALID_DATA']))) {
        throw new RuntimeException('Some products do not have a usable test calculation.');
    }
    return ['database' => 'n07_dss', 'configured' => $products->count(), 'settings' => $settings,
        'states' => $rows->countBy('stock_status')->all(), 'products' => $rows];
});
file_put_contents(storage_path('app/backups/dss-test-settings-result.json'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
