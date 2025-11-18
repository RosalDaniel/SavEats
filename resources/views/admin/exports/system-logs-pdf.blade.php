<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System Logs Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #667eea;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-error { background: #fee2e2; color: #991b1b; }
        .badge-critical { background: #fee2e2; color: #7f1d1d; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-failed { background: #fee2e2; color: #991b1b; }
        .badge-blocked { background: #f3f4f6; color: #4b5563; }
    </style>
</head>
<body>
    <h1>System Logs Export</h1>
    <p><strong>Generated:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
    <p><strong>Total Records:</strong> {{ count($logs) }}</p>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Timestamp</th>
                <th>Event Type</th>
                <th>Severity</th>
                <th>User</th>
                <th>IP Address</th>
                <th>Action</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>#{{ $log->id }}</td>
                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ str_replace('_', ' ', ucwords($log->event_type, '_')) }}</td>
                <td><span class="badge badge-{{ $log->severity }}">{{ $log->severity }}</span></td>
                <td>{{ $log->user_email ?? ($log->user_type ?? 'N/A') }}</td>
                <td>{{ $log->ip_address ?? '-' }}</td>
                <td>{{ $log->action ?? '-' }}</td>
                <td><span class="badge badge-{{ $log->status }}">{{ $log->status }}</span></td>
                <td>{{ $log->description ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center;">No logs found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

