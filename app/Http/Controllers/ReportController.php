<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('request_logs')
            ->leftJoin('api_keys', 'request_logs.api_key_id', '=', 'api_keys.id')
            ->select(
                'request_logs.*',
                'api_keys.name as api_key_name'
            );

        if ($request->filled('status')) {
            $query->where('request_logs.status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('request_logs.created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('request_logs.created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('request_logs.forward_url', 'like', "%{$search}%")
                  ->orWhere('api_keys.name', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderByDesc('request_logs.created_at')->paginate(25)->withQueryString();

        $stats = DB::table('request_logs')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            ")
            ->first();

        return view('report', compact('logs', 'stats'));
    }
}
