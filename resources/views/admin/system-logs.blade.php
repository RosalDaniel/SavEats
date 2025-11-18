@extends('layouts.admin')

@section('title', 'System Logs - Admin Dashboard')

@section('header', 'System Logs')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-system-logs.css') }}">
@endsection

@section('content')
<div class="system-logs-page">
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon total">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Logs</h3>
                <p class="stat-number">{{ number_format($stats['total'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon today">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Today</h3>
                <p class="stat-number">{{ number_format($stats['today'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon critical">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Critical</h3>
                <p class="stat-number">{{ number_format($stats['critical'] ?? 0) }}</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon failed">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Failed Logins (7d)</h3>
                <p class="stat-number">{{ number_format($stats['failed_logins'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="filters-section">
        <div class="filters-row">
            <div class="filter-group">
                <input type="text" class="search-input" placeholder="Search logs..." id="logSearch">
            </div>
            <div class="filter-group">
                <select class="filter-select" id="eventTypeFilter">
                    <option value="">All Event Types</option>
                    <option value="login_attempt">Login Attempt</option>
                    <option value="password_reset">Password Reset</option>
                    <option value="role_change">Role Change</option>
                    <option value="suspicious_activity">Suspicious Activity</option>
                    <option value="account_creation">Account Creation</option>
                    <option value="account_deletion">Account Deletion</option>
                    <option value="data_access">Data Access</option>
                    <option value="system_change">System Change</option>
                </select>
            </div>
            <div class="filter-group">
                <select class="filter-select" id="severityFilter">
                    <option value="">All Severities</option>
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                    <option value="error">Error</option>
                    <option value="critical">Critical</option>
                </select>
            </div>
            <div class="filter-group">
                <select class="filter-select" id="userTypeFilter">
                    <option value="">All User Types</option>
                    <option value="consumer">Consumer</option>
                    <option value="establishment">Establishment</option>
                    <option value="foodbank">Food Bank</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="filter-group">
                <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                    <option value="blocked">Blocked</option>
                </select>
            </div>
        </div>
        <div class="filters-row">
            <div class="filter-group">
                <input type="date" class="filter-input" id="dateFrom" placeholder="From Date">
            </div>
            <div class="filter-group">
                <input type="date" class="filter-input" id="dateTo" placeholder="To Date">
            </div>
            <div class="filter-actions">
                <button class="btn-secondary" onclick="clearFilters()">Clear Filters</button>
                <div class="export-dropdown">
                    <button class="btn-primary" onclick="toggleExportMenu()">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                        </svg>
                        Export
                    </button>
                    <div class="export-menu" id="exportMenu">
                        <a href="#" onclick="exportLogs('csv'); return false;">Export as CSV</a>
                        <a href="#" onclick="exportLogs('pdf'); return false;">Export as PDF</a>
                        <a href="#" onclick="exportLogs('excel'); return false;">Export as Excel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="logs-table-container">
        <table class="logs-table" id="logsTable">
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
            <tbody id="logsTableBody">
                <tr>
                    <td colspan="9" class="loading">Loading logs...</td>
                </tr>
            </tbody>
        </table>
        <div class="pagination-container" id="logsPagination"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Define routes for JavaScript
    const SYSTEM_LOGS_ROUTES = {
        data: '{{ route('admin.system-logs.data') }}',
        exportCsv: '{{ route('admin.system-logs.export.csv') }}',
        exportPdf: '{{ route('admin.system-logs.export.pdf') }}',
        exportExcel: '{{ route('admin.system-logs.export.excel') }}'
    };
</script>
<script src="{{ asset('js/admin-system-logs.js') }}"></script>
@endsection

