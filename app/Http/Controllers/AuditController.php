<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index()
    {
        $logs = DB::table('audit_logs as a')->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->select('a.*', 'u.name')->orderByDesc('a.id')->paginate(30);

        return view('reports.audit', compact('logs'));
    }
}
