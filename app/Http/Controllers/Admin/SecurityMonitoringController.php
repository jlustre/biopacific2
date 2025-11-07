<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecureAccessLog;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SecurityMonitoringController extends Controller
{
    /**
     * Security monitoring dashboard
     */
    public function index(Request $request)
    {
        // Date range filter (default: last 7 days)
        $startDate = $request->get('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        // Facility filter
        $facilityId = $request->get('facility_id');
        
        // Build base query
        $query = SecureAccessLog::whereBetween('access_time', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
        
        if ($facilityId) {
            $query->where('facility_id', $facilityId);
        }

        // Security metrics
        $metrics = $this->getSecurityMetrics($query->clone(), $startDate, $endDate);
        
        // Recent suspicious activities
        $suspiciousActivities = $this->getSuspiciousActivities($query->clone());
        
        // Failed access attempts
        $failedAttempts = $this->getFailedAttempts($query->clone());
        
        // Top accessed records
        $topAccessedRecords = $this->getTopAccessedRecords($query->clone());
        
        // Access patterns by hour
        $accessPatterns = $this->getAccessPatterns($query->clone());
        
        // Geographic analysis
        $ipAnalysis = $this->getIPAnalysis($query->clone());

        // Get facilities for filter
        $facilities = Facility::orderBy('name')->get();

        return view('admin.security-monitoring.index', compact(
            'metrics',
            'suspiciousActivities',
            'failedAttempts',
            'topAccessedRecords',
            'accessPatterns',
            'ipAnalysis',
            'facilities',
            'startDate',
            'endDate',
            'facilityId'
        ));
    }

    /**
     * Detailed security anomalies view
     */
    public function anomalies(Request $request)
    {
        $query = SecureAccessLog::with('facility')
            ->where(function($q) {
                $q->where('access_status', 'unauthorized')
                  ->orWhere('access_status', 'invalid_token')
                  ->orWhere('access_status', 'expired')
                  ->orWhere('access_status', 'unauthorized_email');
            });

        // Apply filters
        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }

        if ($request->filled('status')) {
            $query->where('access_status', $request->status);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', 'LIKE', '%' . $request->ip_address . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('access_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('access_time', '<=', $request->date_to);
        }

        $anomalies = $query->orderBy('access_time', 'desc')->paginate(50);

        // Get facilities for filter
        $facilities = Facility::orderBy('name')->get();

        // Anomaly statistics
        $stats = [
            'total_anomalies' => $query->count(),
            'unique_ips' => $query->distinct('ip_address')->count(),
            'facilities_affected' => $query->distinct('facility_id')->count(),
            'recent_24h' => $query->where('access_time', '>', Carbon::now()->subDay())->count()
        ];

        return view('admin.security-monitoring.anomalies', compact(
            'anomalies',
            'facilities',
            'stats'
        ));
    }

    /**
     * Detailed access logs for a specific record
     */
    public function recordLogs(Request $request, $tokenType, $recordId)
    {
        $logs = SecureAccessLog::where('token_type', $tokenType)
            ->where('record_id', $recordId)
            ->with('facility')
            ->orderBy('access_time', 'desc')
            ->get();

        // Determine record details based on type
        $record = null;
        switch ($tokenType) {
            case 'inquiry':
                $record = \App\Models\Inquiry::find($recordId);
                break;
            case 'tour_request':
                $record = \App\Models\TourRequest::find($recordId);
                break;
            case 'job_application':
                $record = \App\Models\JobApplication::find($recordId);
                break;
        }

        // Analyze access patterns
        $analysis = $this->analyzeRecordAccess($logs);

        return view('admin.security-monitoring.record-logs', compact(
            'logs',
            'record',
            'tokenType',
            'recordId',
            'analysis'
        ));
    }

    /**
     * Security incident report
     */
    public function incidents(Request $request)
    {
        // Define incidents based on suspicious patterns
        $incidents = collect();

        // Get records with multiple failed attempts
        $multipleFailures = SecureAccessLog::select('token_type', 'record_id', 'facility_id')
            ->where('access_status', 'unauthorized')
            ->where('access_time', '>', Carbon::now()->subDays(7))
            ->groupBy(['token_type', 'record_id', 'facility_id'])
            ->havingRaw('COUNT(*) >= 3')
            ->with('facility')
            ->get();

        foreach ($multipleFailures as $failure) {
            $incidents->push([
                'type' => 'multiple_failures',
                'severity' => 'high',
                'description' => "Multiple unauthorized access attempts on {$failure->token_type} #{$failure->record_id}",
                'facility' => $failure->facility,
                'count' => SecureAccessLog::where('token_type', $failure->token_type)
                    ->where('record_id', $failure->record_id)
                    ->where('access_status', 'unauthorized')
                    ->count(),
                'latest' => SecureAccessLog::where('token_type', $failure->token_type)
                    ->where('record_id', $failure->record_id)
                    ->latest('access_time')
                    ->first()
            ]);
        }

        // Get suspicious IP patterns
        $suspiciousIPs = SecureAccessLog::select('ip_address')
            ->where('access_time', '>', Carbon::now()->subDays(7))
            ->groupBy('ip_address')
            ->havingRaw('COUNT(DISTINCT facility_id) > 3') // Accessing multiple facilities
            ->orHavingRaw('COUNT(*) > 20') // High access volume
            ->get();

        foreach ($suspiciousIPs as $ip) {
            $accessCount = SecureAccessLog::where('ip_address', $ip->ip_address)->count();
            $facilityCount = SecureAccessLog::where('ip_address', $ip->ip_address)
                ->distinct('facility_id')->count();

            $incidents->push([
                'type' => 'suspicious_ip',
                'severity' => $facilityCount > 5 ? 'critical' : 'medium',
                'description' => "IP {$ip->ip_address} accessed {$facilityCount} facilities with {$accessCount} total attempts",
                'ip_address' => $ip->ip_address,
                'access_count' => $accessCount,
                'facility_count' => $facilityCount,
                'latest' => SecureAccessLog::where('ip_address', $ip->ip_address)
                    ->latest('access_time')
                    ->first()
            ]);
        }

        // Sort by severity and time
        $incidents = $incidents->sortByDesc(function($incident) {
            $severityOrder = ['critical' => 3, 'high' => 2, 'medium' => 1, 'low' => 0];
            return $severityOrder[$incident['severity']] * 1000 + ($incident['latest']->access_time ?? now())->timestamp;
        });

        // Get facilities for filter dropdown
        $facilities = Facility::orderBy('name')->get();

        return view('admin.security-monitoring.incidents', compact('incidents', 'facilities'));
    }

    /**
     * Export security report
     */
    public function exportReport(Request $request)
    {
        $startDate = Carbon::parse($request->get('start_date', Carbon::now()->subDays(30)->format('Y-m-d')));
        $endDate = Carbon::parse($request->get('end_date', Carbon::now()->format('Y-m-d')));

        $logs = SecureAccessLog::with('facility')
            ->whereBetween('access_time', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('access_time', 'desc')
            ->get();

        $filename = 'security_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Date/Time',
                'Facility',
                'Record Type',
                'Record ID',
                'Access Status',
                'Staff Email',
                'IP Address',
                'User Agent',
                'Request Headers'
            ]);

            // CSV Data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->access_time->format('Y-m-d H:i:s'),
                    $log->facility->name ?? 'N/A',
                    $log->token_type,
                    $log->record_id,
                    $log->access_status,
                    $log->staff_email ?? 'N/A',
                    $log->ip_address,
                    $log->user_agent,
                    json_encode($log->request_headers)
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get security metrics
     */
    private function getSecurityMetrics($query, $startDate, $endDate)
    {
        $totalAccess = $query->count();
        $successfulAccess = $query->clone()->where('access_status', 'success')->count();
        $failedAccess = $query->clone()->whereIn('access_status', ['unauthorized', 'invalid_token', 'expired', 'unauthorized_email'])->count();
        $uniqueIPs = $query->clone()->distinct('ip_address')->count();
        $verifiedStaff = $query->clone()->whereNotNull('staff_email')->distinct('staff_email')->count();

        return [
            'total_access' => $totalAccess,
            'successful_access' => $successfulAccess,
            'failed_access' => $failedAccess,
            'success_rate' => $totalAccess > 0 ? round(($successfulAccess / $totalAccess) * 100, 1) : 0,
            'unique_ips' => $uniqueIPs,
            'verified_staff' => $verifiedStaff,
            'date_range' => Carbon::parse($startDate)->format('M j, Y') . ' - ' . Carbon::parse($endDate)->format('M j, Y')
        ];
    }

    /**
     * Get suspicious activities
     */
    private function getSuspiciousActivities($query)
    {
        return $query->where(function($q) {
            $q->where('access_status', 'unauthorized')
              ->orWhere('access_status', 'unauthorized_email');
        })
        ->with('facility')
        ->orderBy('access_time', 'desc')
        ->limit(10)
        ->get();
    }

    /**
     * Get failed access attempts
     */
    private function getFailedAttempts($query)
    {
        return $query->whereIn('access_status', ['invalid_token', 'expired'])
            ->with('facility')
            ->orderBy('access_time', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get top accessed records
     */
    private function getTopAccessedRecords($query)
    {
        return $query->select('token_type', 'record_id', 'facility_id')
            ->selectRaw('COUNT(*) as access_count')
            ->selectRaw('MAX(access_time) as latest_access')
            ->with('facility')
            ->groupBy(['token_type', 'record_id', 'facility_id'])
            ->orderByDesc('access_count')
            ->limit(10)
            ->get();
    }

    /**
     * Get access patterns by hour
     */
    private function getAccessPatterns($query)
    {
        return $query->selectRaw('HOUR(access_time) as hour, COUNT(*) as count')
            ->groupByRaw('HOUR(access_time)')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();
    }

    /**
     * Get IP analysis
     */
    private function getIPAnalysis($query)
    {
        return $query->select('ip_address')
            ->selectRaw('COUNT(*) as access_count')
            ->selectRaw('COUNT(DISTINCT facility_id) as facility_count')
            ->selectRaw('MAX(access_time) as latest_access')
            ->groupBy('ip_address')
            ->orderByDesc('access_count')
            ->limit(20)
            ->get();
    }

    /**
     * Analyze access patterns for a specific record
     */
    private function analyzeRecordAccess($logs)
    {
        return [
            'total_accesses' => $logs->count(),
            'unique_ips' => $logs->pluck('ip_address')->unique()->count(),
            'successful_accesses' => $logs->where('access_status', 'success')->count(),
            'failed_accesses' => $logs->whereIn('access_status', ['unauthorized', 'invalid_token', 'expired'])->count(),
            'verified_staff' => $logs->whereNotNull('staff_email')->pluck('staff_email')->unique()->count(),
            'first_access' => $logs->min('access_time'),
            'latest_access' => $logs->max('access_time'),
            'access_span_hours' => $logs->count() > 1 ? 
                Carbon::parse($logs->max('access_time'))->diffInHours(Carbon::parse($logs->min('access_time'))) : 0
        ];
    }
}
