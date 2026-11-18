<?php

// One authorized test batch on the configured MySQL database, not a UI fixture.
// Preview: php scripts/seed_inventory_test.php
// Apply:   php scripts/seed_inventory_test.php --apply
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::create('http://localhost'));
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();

use App\Http\Controllers\InventoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$batch = 'TEST-N07-20261118';
if (DB::connection()->getDriverName() !== 'mysql' || DB::connection()->getDatabaseName() !== 'n07_dss') {
    throw new RuntimeException('Expected the configured group MySQL database n07_dss. No data written.');
}
if (DB::table('inventory_transactions')->where('reference_no', 'like', $batch.'-%')->exists()) {
    echo "Batch already exists; no duplicate vouchers created.\n";
    exit(0);
}
$products = DB::table('products')->where('status', 1)->orderBy('id')->get();
$actorId = DB::table('users')->where('is_active', 1)->where('role', 'admin')->orderBy('id')->value('id');
if ($products->count() < 3 || ! $actorId) {
    throw new RuntimeException('At least three active products and one active admin are required.');
}
echo 'Database: '.DB::connection()->getDatabaseName().'; products: '.$products->count()."; batch: {$batch}\n";
if (! in_array('--apply', $argv, true)) {
    echo "Plan: 10 imports + 10 exports, 3 product lines per voucher, 2026-11-08 through 2026-11-17. Use --apply to persist.\n";
    exit(0);
}

Auth::loginUsingId($actorId);
$session = $app['session']->driver();
$session->start();
$directory = storage_path('app/backups');
if (! is_dir($directory)) {
    mkdir($directory, 0700, true);
}
file_put_contents($directory.'/.htaccess', "Require all denied\n");
$report = DB::transaction(function () use ($app, $batch, $actorId, $session, $directory) {
    $products = DB::table('products')->where('status', 1)->orderBy('id')->lockForUpdate()->get();
    $before = DB::table('v_stock_balances')->pluck('on_hand', 'product_id')->all();
    $backup = ['database' => DB::connection()->getDatabaseName(), 'batch' => $batch, 'created_at' => now()->toIso8601String(), 'stock_before' => $before];
    foreach (['inventory_transactions', 'inventory_transaction_items', 'reorder_recommendations', 'audit_logs'] as $table) {
        $backup[$table] = DB::table($table)->get()->all();
    }
    $path = $directory.'/before-'.$batch.'-'.now()->format('YmdHis').'.json';
    if (file_put_contents($path, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) === false) {
        throw new RuntimeException('Could not save pre-import snapshot.');
    }
    $deltas = [];
    $vouchers = [];
    for ($i = 0; $i < 10; $i++) {
        $date = Illuminate\Support\Carbon::parse('2026-11-08')->addDays($i)->toDateString();
        $purpose = $i === 8 ? 'damage' : ($i === 9 ? 'transfer' : 'sale');
        foreach (['import', 'export'] as $type) {
            $items = [];
            for ($line = 0; $line < 3; $line++) {
                $product = $products[($i * 3 + $line) % $products->count()];
                $received = 40 + $i * 5 + $line * 10;
                $quantity = $type === 'import' ? $received : ($purpose === 'sale' ? $received - [0, 5, 20][$line] : 2 + $line);
                $price = $type === 'import' || $purpose !== 'sale' ? $product->purchase_price : $product->selling_price;
                $items[] = ['product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => $price];
                $deltas[$product->id] = ($deltas[$product->id] ?? 0) + ($type === 'import' ? $quantity : -$quantity);
            }
            $reference = $batch.'-'.($type === 'import' ? 'PN' : 'PX').'-'.str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            $payload = ['transaction_date' => $date, 'reference_no' => $reference,
                'note' => '[KIỂM THỬ N07] Dữ liệu mô phỏng được nhóm cho phép ghi vào DB thật; không phải chứng từ mua bán thực tế. Đợt '.$batch,
                'items' => $items];
            if ($type === 'export') {
                $payload['issue_purpose'] = $purpose;
            }
            $request = Request::create('/inventory/'.$type, 'POST', $payload);
            $request->setLaravelSession($session);
            $request->setUserResolver(fn () => Auth::user());
            $app->instance('request', $request);
            $response = app(InventoryController::class)->store($request, $type);
            if ($response->getStatusCode() !== 302) {
                throw new RuntimeException('Voucher was not accepted: '.$reference);
            }
            $row = DB::table('inventory_transactions')->where('reference_no', $reference)->first();
            if (! $row) {
                throw new RuntimeException('Voucher missing after creation: '.$reference);
            }
            $vouchers[] = ['id' => $row->id, 'code' => $row->transaction_code, 'reference' => $reference, 'date' => $date, 'type' => $type, 'purpose' => $row->issue_purpose];
        }
    }
    $after = DB::table('v_stock_balances')->pluck('on_hand', 'product_id')->all();
    foreach ($deltas as $id => $delta) {
        if ((int) $after[$id] !== (int) ($before[$id] ?? 0) + $delta || $after[$id] < 0) {
            throw new RuntimeException('Stock reconciliation failed for product '.$id);
        }
    }
    $ids = array_column($vouchers, 'id');
    $mismatches = DB::table('inventory_transactions as t')->join('inventory_transaction_items as i', 't.id', '=', 'i.inventory_transaction_id')
        ->whereIn('t.id', $ids)->select('t.id')->groupBy('t.id', 't.total_value')->havingRaw('ABS(t.total_value - SUM(i.line_total)) > 0.01')->get()->count();
    if ($mismatches) {
        throw new RuntimeException('Voucher value reconciliation failed.');
    }
    return ['database' => DB::connection()->getDatabaseName(), 'batch' => $batch,
        'imports' => 10, 'exports' => 10, 'lines' => 60, 'actor_id' => $actorId,
        'stock_before' => $before, 'stock_after' => $after, 'vouchers' => $vouchers];
});
file_put_contents(storage_path('app/backups/'.$batch.'-result.json'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
echo json_encode(['database' => $report['database'], 'batch' => $batch, 'imports' => 10, 'exports' => 10, 'lines' => 60, 'stock_verified' => true], JSON_UNESCAPED_UNICODE).PHP_EOL;
