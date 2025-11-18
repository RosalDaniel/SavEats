<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemLogController extends Controller
{
    /**
     * Get user data helper
     */
    private function getUserData()
    {
        return [
            'id' => session('user_id'),
            'username' => session('user_name'),
            'email' => session('user_email'),
            'type' => session('user_type'),
        ];
    }

    /**
     * Display system logs page
     */
    public function index()
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied. Please login as an admin.');
        }

        $user = $this->getUserData();
        
        // Get statistics
        $stats = [
            'total' => SystemLog::count(),
            'today' => SystemLog::whereDate('created_at', today())->count(),
            'critical' => SystemLog::where('severity', 'critical')->count(),
            'failed_logins' => SystemLog::where('event_type', 'login_attempt')
                ->where('status', 'failed')
                ->whereDate('created_at', '>=', Carbon::now()->subDays(7))
                ->count(),
        ];

        return view('admin.system-logs', compact('user', 'stats'));
    }

    /**
     * Get logs with filters (AJAX)
     */
    public function getLogs(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $query = SystemLog::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('event_type', 'like', '%' . $search . '%')
                  ->orWhere('action', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('user_email', 'like', '%' . $search . '%')
                  ->orWhere('ip_address', 'like', '%' . $search . '%');
            });
        }

        // Filter by event type
        if ($request->has('event_type') && $request->event_type) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by severity
        if ($request->has('severity') && $request->severity) {
            $query->where('severity', $request->severity);
        }

        // Filter by user type
        if ($request->has('user_type') && $request->user_type) {
            $query->where('user_type', $request->user_type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Order by created_at desc
        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json(['success' => true, 'data' => $logs]);
    }

    /**
     * Export logs to CSV
     */
    public function exportCsv(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }

        $query = $this->buildFilterQuery($request);

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'system_logs_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Event Type',
                'Severity',
                'User Type',
                'User ID',
                'User Email',
                'IP Address',
                'Action',
                'Status',
                'Description',
                'Created At'
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->event_type,
                    $log->severity,
                    $log->user_type ?? 'N/A',
                    $log->user_id ?? 'N/A',
                    $log->user_email ?? 'N/A',
                    $log->ip_address ?? 'N/A',
                    $log->action ?? 'N/A',
                    $log->status,
                    $log->description ?? 'N/A',
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export logs to PDF
     */
    public function exportPdf(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }

        $query = $this->buildFilterQuery($request);
        $logs = $query->orderBy('created_at', 'desc')->get();

        // Simple HTML-based PDF (you can use DomPDF or similar for better PDF generation)
        $html = view('admin.exports.system-logs-pdf', compact('logs'))->render();

        $filename = 'system_logs_' . date('Y-m-d_His') . '.html';

        return response($html, 200)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Export logs to Excel (CSV format with Excel MIME type)
     */
    public function exportExcel(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }

        $query = $this->buildFilterQuery($request);
        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'system_logs_' . date('Y-m-d_His') . '.xlsx';

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // For now, we'll use CSV format with Excel MIME type
        // For proper Excel export, install maatwebsite/excel package
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Event Type',
                'Severity',
                'User Type',
                'User ID',
                'User Email',
                'IP Address',
                'Action',
                'Status',
                'Description',
                'Created At'
            ]);

            // CSV data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->event_type,
                    $log->severity,
                    $log->user_type ?? 'N/A',
                    $log->user_id ?? 'N/A',
                    $log->user_email ?? 'N/A',
                    $log->ip_address ?? 'N/A',
                    $log->action ?? 'N/A',
                    $log->status,
                    $log->description ?? 'N/A',
                    $log->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build filtered query based on request parameters
     */
    private function buildFilterQuery(Request $request)
    {
        $query = SystemLog::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('event_type', 'like', '%' . $search . '%')
                  ->orWhere('action', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('user_email', 'like', '%' . $search . '%')
                  ->orWhere('ip_address', 'like', '%' . $search . '%');
            });
        }

        // Filter by event type
        if ($request->has('event_type') && $request->event_type) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by severity
        if ($request->has('severity') && $request->severity) {
            $query->where('severity', $request->severity);
        }

        // Filter by user type
        if ($request->has('user_type') && $request->user_type) {
            $query->where('user_type', $request->user_type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    /**
     * Delete old logs (older than specified days)
     */
    public function deleteOldLogs(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $days = $request->input('days', 90); // Default 90 days
        $cutoffDate = Carbon::now()->subDays($days);

        $deleted = SystemLog::where('created_at', '<', $cutoffDate)->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} log entries older than {$days} days."
        ]);
    }
}
