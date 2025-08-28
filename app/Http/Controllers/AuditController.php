<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', "%{$request->model_type}%");
        }

        $logs = $query->paginate(20);

        // Get filter options
        $actions = AuditLog::distinct()->pluck('action')->sort()->values();
        $severities = ['low', 'medium', 'high', 'critical'];
        $users = AuditLog::distinct()->whereNotNull('user_name')->pluck('user_name')->sort()->values();
        $models = AuditLog::distinct()->whereNotNull('model_type')
            ->get(['model_type'])
            ->map(fn($log) => class_basename($log->model_type))
            ->unique()
            ->sort()
            ->values();

        return view('audit.index', compact('logs', 'actions', 'severities', 'users', 'models'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        return view('audit.show', compact('auditLog'));
    }

    public function export(Request $request): JsonResponse
    {
        // This would implement CSV/Excel export functionality
        return response()->json(['message' => 'Export functionality would be implemented here']);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total_logs' => AuditLog::count(),
            'today_logs' => AuditLog::whereDate('created_at', today())->count(),
            'critical_logs' => AuditLog::where('severity', 'critical')->count(),
            'recent_actions' => AuditLog::select('action')
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->groupBy('action')
                ->selectRaw('action, count(*) as count')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
        ];

        return response()->json($stats);
    }
}
