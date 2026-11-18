<?php

namespace App\Http\Controllers;

use App\Services\DemandForecastService;
use App\Services\ReorderRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ForecastController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['product_id' => 'nullable|integer|exists:products,id']);
        $products = DB::table('products')->where('status', 1)->orderBy('name')->get();
        $runs = DB::table('forecast_runs as f')->join('products as p', 'f.product_id', '=', 'p.id')
            ->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('p.id', $id))
            ->select('f.*', 'p.name', 'p.product_code', 'p.unit')->orderByDesc('f.id')->paginate(20)->withQueryString();

        return view('reports.forecast', compact('products', 'runs'));
    }

    public function store(Request $request, DemandForecastService $forecast)
    {
        $data = $request->validate(['product_id' => 'required|integer|exists:products,id']);
        $setting = DB::table('product_reorder_settings')->where('product_id', $data['product_id'])->first();
        $end = now()->subDay()->toDateString();
        if (! $setting?->observation_start || $setting->observation_start > now()->subDays($setting->ma_days + 7)->toDateString()) {
            throw ValidationException::withMessages(['product_id' => 'Chưa đủ dữ liệu: cần cửa sổ MA cộng 7 ngày kiểm thử đã kết thúc. Hãy kiểm tra cấu hình DSS.']);
        }
        $start = now()->subDays($setting->ma_days + 7)->toDateString();
        $result = $forecast->evaluate($forecast->series($data['product_id'], $start, $end), $setting->ma_days);
        DB::table('forecast_runs')->insert([
            'product_id' => $data['product_id'], 'created_by' => $request->user()->id,
            'period_start' => $start, 'period_end' => $end, 'ma_days' => $setting->ma_days,
            'model_version' => 'linear-trend-v1', 'results' => json_encode($result),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        app(ReorderRecommendationService::class)->refreshIfNeeded((int) $request->user()->id);

        return redirect()->route('reports.bc05', ['product_id' => $data['product_id']])->with('success', 'Đã lưu kết quả đánh giá MA và Linear Regression trên cùng 7 ngày kiểm thử.');
    }
}
