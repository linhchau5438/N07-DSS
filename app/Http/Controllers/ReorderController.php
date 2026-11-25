<?php

namespace App\Http\Controllers;

use App\Services\AuditLog;
use App\Services\DemandForecastService;
use App\Services\ReorderRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReorderController extends Controller
{
    private const LOOKBACK_DAYS = 30;

    public function index()
    {
        app(ReorderRecommendationService::class)->refreshIfNeeded((int) auth()->id());

        $latestRecommendations = DB::table('reorder_recommendations')
            ->select('product_id', DB::raw('MAX(id) as latest_id'))
            ->groupBy('product_id');

        $pendingCount = DB::table('reorder_recommendations as recommendations')
            ->joinSub($latestRecommendations, 'latest', function ($join) {
                $join->on('recommendations.id', '=', 'latest.latest_id');
            })
            ->join('products as active_product', 'recommendations.product_id', '=', 'active_product.id')
            ->where('active_product.status', 1)
            ->where('recommendations.status', 'pending')
            ->where('recommendations.suggested_quantity', '>', 0)
            ->count();
        $lastCalculatedAt = DB::table('reorder_recommendations')->max('created_at');

        // Đơn hàng thực tế đã xác nhận đặt với NCC và đang chờ nhận hàng (On Order)
        $openOrders = DB::table('reorder_recommendations')
            ->join('products', 'reorder_recommendations.product_id', '=', 'products.id')
            ->whereIn('reorder_recommendations.status', ['ordered', 'partially_received'])
            ->whereColumn('reorder_recommendations.ordered_quantity', '>', 'reorder_recommendations.received_quantity')
            ->orderBy('products.name')
            ->get([
                'reorder_recommendations.id',
                'reorder_recommendations.product_id',
                'reorder_recommendations.approved_quantity',
                'reorder_recommendations.ordered_quantity',
                'reorder_recommendations.received_quantity',
                'reorder_recommendations.status',
                'reorder_recommendations.supplier_name',
                'reorder_recommendations.expected_delivery_date',
                'products.product_code',
                'products.name',
                'products.unit',
            ]);

        // Đề xuất đã được Admin duyệt, đang chờ nhân viên liên hệ xác nhận đặt hàng với NCC
        $approvedOrders = DB::table('reorder_recommendations')
            ->join('products', 'reorder_recommendations.product_id', '=', 'products.id')
            ->where('reorder_recommendations.status', 'approved')
            ->orderBy('products.name')
            ->get([
                'reorder_recommendations.id',
                'reorder_recommendations.product_id',
                'reorder_recommendations.suggested_quantity',
                'reorder_recommendations.approved_quantity',
                'reorder_recommendations.decision_note',
                'products.product_code',
                'products.name',
                'products.unit',
            ]);

        $products = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('product_reorder_settings', 'products.id', '=', 'product_reorder_settings.product_id')
            ->leftJoinSub($this->stockTotalsQuery(), 'stock_totals', function ($join) {
                $join->on('products.id', '=', 'stock_totals.product_id');
            })
            ->leftJoinSub($latestRecommendations, 'latest', function ($join) {
                $join->on('products.id', '=', 'latest.product_id');
            })
            ->leftJoin('reorder_recommendations as recommendations', 'latest.latest_id', '=', 'recommendations.id')
            ->select(
                'products.id',
                'products.product_code',
                'products.name',
                'products.unit',
                'categories.name as category_name',
                DB::raw('COALESCE(product_reorder_settings.lead_time_days, 7) as lead_time_days'),
                DB::raw('COALESCE(product_reorder_settings.target_days, 30) as target_days'),
                DB::raw('COALESCE(product_reorder_settings.safety_factor, 1.65) as safety_factor'),
                'product_reorder_settings.ma_days', 'product_reorder_settings.safety_mode', 'product_reorder_settings.manual_safety_stock', 'product_reorder_settings.pack_size', 'product_reorder_settings.observation_start', 'product_reorder_settings.forecast_method',
                'recommendations.stock_status', 'recommendations.calculation_snapshot',
                'recommendations.id as recommendation_id',
                'recommendations.period_start',
                'recommendations.period_end',
                'recommendations.average_daily_demand',
                'recommendations.safety_stock',
                'recommendations.reorder_point',
                'recommendations.on_hand',
                'recommendations.on_order',
                'recommendations.committed',
                'recommendations.inventory_position',
                'recommendations.suggested_quantity',
                'recommendations.approved_quantity',
                'recommendations.ordered_quantity',
                'recommendations.received_quantity',
                'recommendations.supplier_name',
                'recommendations.expected_delivery_date',
                'recommendations.order_note',
                'recommendations.cancellation_reason',
                'recommendations.status as recommendation_status',
                'recommendations.explanation',
                'recommendations.decision_note',
            )
            ->selectRaw('COALESCE(stock_totals.on_hand, 0) as current_on_hand')
            ->where('products.status', 1)
            ->orderByRaw("CASE WHEN recommendations.status = 'pending' AND recommendations.suggested_quantity > 0 THEN 0 ELSE 1 END")
            ->orderBy('products.name')
            ->paginate(15);

        return view('dss.index', compact('products', 'pendingCount', 'lastCalculatedAt', 'openOrders', 'approvedOrders'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'period_days' => 'required|integer|in:30',
        ]);

        $updated = app(ReorderRecommendationService::class)->refreshIfNeeded((int) $request->user()->id);

        return redirect()->route('dss.index')
            ->with('success', $updated > 0
                ? 'Đã tự cập nhật đề xuất theo dữ liệu kho mới nhất.'
                : 'Đề xuất đã được cập nhật theo dữ liệu mới nhất.');
    }

    public function updateSettings(Request $request, int $productId)
    {
        if (! DB::table('products')->where('id', $productId)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'lead_time_days' => 'required|integer|min:0|max:365',
            'ma_days' => 'required|integer|in:7,14,30',
            'safety_mode' => 'required|in:manual,statistical',
            'manual_safety_stock' => 'required|integer|min:0|max:1000000',
            'pack_size' => 'required|integer|min:1|max:1000000',
            'observation_start' => 'required|date|before_or_equal:today',
            'forecast_method' => 'required|in:ma,auto',
            'target_days' => 'required|integer|min:0|max:365',
            'safety_factor' => 'required|numeric|min:0|max:3',
        ]);

        $settingsExist = DB::table('product_reorder_settings')
            ->where('product_id', $productId)
            ->exists();
        $settingValues = $validated + [
            'updated_by' => $request->user()->id,
            'lead_time_days' => $validated['lead_time_days'],
            'target_days' => $validated['target_days'],
            'safety_factor' => $validated['safety_factor'],
            'updated_at' => now(),
        ];
        if (! $settingsExist) {
            $settingValues['created_at'] = now();
        }

        $before = DB::table('product_reorder_settings')->where('product_id', $productId)->first();
        DB::table('product_reorder_settings')->updateOrInsert(
            ['product_id' => $productId],
            $settingValues,
        );

        AuditLog::record('UPDATE_DSS_SETTINGS', 'products', $productId, $before, $settingValues);
        app(ReorderRecommendationService::class)->refreshIfNeeded((int) $request->user()->id);

        return redirect()->route('dss.index')
            ->with('success', 'Đã cập nhật thông số DSS và tính lại đề xuất.');
    }

    public function syncLeadTime(Request $request, int $id)
    {
        $stats = app(DemandForecastService::class)->actualLeadTimeStats($id);
        if (! $stats['has_history'] || empty($stats['avg_lead_time_days'])) {
            return redirect()->route('dss.index')
                ->with('error', 'Chưa có đủ lịch sử đối soát phiếu nhập theo đơn đặt hàng để tính Lead Time thực tế.');
        }

        $avgLead = $stats['avg_lead_time_days'];
        $before = DB::table('product_reorder_settings')->where('product_id', $id)->first();

        DB::table('product_reorder_settings')->updateOrInsert(
            ['product_id' => $id],
            [
                'lead_time_days' => $avgLead,
                'updated_by' => $request->user()->id,
                'updated_at' => now(),
            ]
        );

        AuditLog::record('SYNC_ACTUAL_LEAD_TIME', 'product_reorder_settings', $id, $before, [
            'lead_time_days' => $avgLead,
            'stats' => $stats,
        ]);

        app(ReorderRecommendationService::class)->refreshIfNeeded((int) $request->user()->id);

        return redirect()->route('dss.index')
            ->with('success', "Đã đồng bộ Lead Time kế hoạch thành {$avgLead} ngày (dựa trên {$stats['sample_count']} lần nhập hàng thực tế).");
    }

    public function decide(Request $request, int $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $validated = $request->validate([
                'decision' => 'required|in:approved,rejected',
                'approved_quantity' => 'nullable|integer|min:1|max:1000000',
                'decision_note' => 'nullable|string|max:1000',
            ]);

            app(ReorderRecommendationService::class)->refreshIfNeeded((int) $request->user()->id);
            $recommendation = DB::table('reorder_recommendations')->find($id);

            if (! $recommendation) {
                abort(404);
            }

            if ($recommendation->status !== 'pending') {
                return redirect()->route('dss.index')
                    ->with('error', 'Đề xuất này đã được xử lý hoặc không cần nhập.');
            }

            $approvedQuantity = null;
            if ($validated['decision'] === 'approved') {
                $approvedQuantity = $validated['approved_quantity'] ?? $recommendation->suggested_quantity;
                if ($approvedQuantity < 1) {
                    throw ValidationException::withMessages([
                        'approved_quantity' => 'Số lượng duyệt phải lớn hơn 0.',
                    ]);
                }
            }

            if (($validated['decision'] === 'rejected' || $approvedQuantity != $recommendation->suggested_quantity) && empty(trim($validated['decision_note'] ?? ''))) {
                throw ValidationException::withMessages(['decision_note' => 'Cần ghi lý do khi điều chỉnh hoặc từ chối đề xuất.']);
            }

            $updated = DB::table('reorder_recommendations')
                ->where('id', $id)
                ->where('status', 'pending')
                ->update([
                    'status' => $validated['decision'],
                    'approved_quantity' => $approvedQuantity,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'decision_note' => $validated['decision_note'] ?? null,
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                return redirect()->route('dss.index')
                    ->with('error', 'Đề xuất vừa được xử lý bởi một thao tác khác. Hãy tải lại trang.');
            }

            return redirect()->route('dss.index')
                ->with('success', $validated['decision'] === 'approved'
                    ? 'Đã duyệt đề xuất nhập hàng (Đang chờ xác nhận đặt hàng với NCC).'
                    : 'Đã từ chối đề xuất nhập hàng.');
        });
    }

    public function confirmOrder(Request $request, int $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $recommendation = DB::table('reorder_recommendations')->where('id', $id)->lockForUpdate()->first();
            if (! $recommendation) {
                abort(404);
            }
            if ($recommendation->status !== 'approved') {
                return redirect()->route('dss.index')->with('error', 'Chỉ đề xuất đã được Admin duyệt mới có thể xác nhận đặt hàng với NCC.');
            }

            $validated = $request->validate([
                'ordered_quantity' => 'required|integer|min:1|max:'.(int) $recommendation->approved_quantity,
                'expected_delivery_date' => 'nullable|date|after_or_equal:today',
                'supplier_name' => 'nullable|string|max:150',
                'order_note' => 'nullable|string|max:1000',
            ], [
                'ordered_quantity.required' => 'Vui lòng nhập số lượng thực tế đặt với NCC.',
                'ordered_quantity.max' => 'Số lượng đặt không được vượt quá số lượng Admin đã duyệt ('.$recommendation->approved_quantity.').',
                'expected_delivery_date.after_or_equal' => 'Ngày dự kiến nhận không được ở quá khứ.',
            ]);

            if ((int) $validated['ordered_quantity'] < (int) $recommendation->approved_quantity && empty(trim($validated['order_note'] ?? ''))) {
                throw ValidationException::withMessages([
                    'order_note' => 'Vui lòng ghi rõ lý do khi số lượng đặt thực tế nhỏ hơn số lượng đã duyệt (ví dụ: NCC hết hàng, chỉ đáp ứng một phần).',
                ]);
            }

            DB::table('reorder_recommendations')->where('id', $id)->update([
                'status' => 'ordered',
                'ordered_quantity' => $validated['ordered_quantity'],
                'on_order' => $validated['ordered_quantity'],
                'ordered_by' => $request->user()->id,
                'ordered_at' => now(),
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'supplier_name' => $validated['supplier_name'] ?? null,
                'order_note' => $validated['order_note'] ?? null,
                'updated_at' => now(),
            ]);

            AuditLog::record('CONFIRM_PURCHASE_ORDER', 'reorder_recommendations', $id, ['status' => 'approved'], $validated);
            app(ReorderRecommendationService::class)->refreshIfNeeded((int) $request->user()->id);

            return redirect()->route('dss.index')->with('success', 'Đã xác nhận đặt hàng thực tế với NCC. Lượng hàng đang về đã được cập nhật.');
        });
    }

    public function cancel(Request $request, int $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $recommendation = DB::table('reorder_recommendations')->where('id', $id)->lockForUpdate()->first();
            if (! $recommendation) {
                abort(404);
            }
            if (! in_array($recommendation->status, ['approved', 'ordered', 'partially_received'], true)) {
                return redirect()->route('dss.index')->with('error', 'Không thể hủy đề xuất ở trạng thái này.');
            }

            $validated = $request->validate([
                'cancellation_reason' => 'required|string|max:1000',
            ], [
                'cancellation_reason.required' => 'Vui lòng nhập lý do hủy đơn (ví dụ: NCC báo hết hàng, hủy phần chưa giao...).',
            ]);

            DB::table('reorder_recommendations')->where('id', $id)->update([
                'status' => 'cancelled',
                'on_order' => 0,
                'cancelled_by' => $request->user()->id,
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellation_reason'],
                'updated_at' => now(),
            ]);

            AuditLog::record('CANCEL_REORDER_RECOMMENDATION', 'reorder_recommendations', $id, ['status' => $recommendation->status], $validated);
            app(ReorderRecommendationService::class)->refreshIfNeeded((int) $request->user()->id);

            return redirect()->route('dss.index')->with('success', 'Đã hủy đơn đặt hàng. Lượng hàng đang về đã được hoàn nguyên an toàn.');
        });
    }

    public function history()
    {
        $records = DB::table('reorder_recommendations as r')
            ->join('products as p', 'r.product_id', '=', 'p.id')
            ->leftJoin('users as u', 'r.reviewed_by', '=', 'u.id')
            ->select('r.*', 'p.name', 'p.product_code', 'p.unit', 'u.name as reviewer')
            ->orderByDesc('r.id')->paginate(30);

        return view('dss.history', compact('records'));
    }

    private function stockTotalsQuery()
    {
        return DB::table('inventory_transaction_items')
            ->join('inventory_transactions', 'inventory_transaction_items.inventory_transaction_id', '=', 'inventory_transactions.id')
            ->select('inventory_transaction_items.product_id')
            ->selectRaw("SUM(CASE WHEN inventory_transactions.type = 'import' THEN inventory_transaction_items.quantity ELSE -inventory_transaction_items.quantity END) as on_hand")
            ->groupBy('inventory_transaction_items.product_id');
    }
}
