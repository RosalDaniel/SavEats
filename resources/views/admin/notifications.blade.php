@extends('layouts.admin')

@section('title', 'Admin Notifications')

@section('header', 'Admin Notifications')

@section('content')
<div class="notifications-page">
    <div class="notifications-header">
        <div class="header-actions-panel">
            <div class="filter-group">
                <label for="filterStatus">Status:</label>
                <select id="filterStatus" class="filter-select">
                    <option value="all" {{ request('filter') === 'all' || !request('filter') ? 'selected' : '' }}>All</option>
                    <option value="unread" {{ request('filter') === 'unread' ? 'selected' : '' }}>Unread</option>
                    <option value="read" {{ request('filter') === 'read' ? 'selected' : '' }}>Read</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filterType">Type:</label>
                <select id="filterType" class="filter-select">
                    <option value="all" {{ !request('type') || request('type') === 'all' ? 'selected' : '' }}>All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="filter-group">
                <label for="filterPriority">Priority:</label>
                <select id="filterPriority" class="filter-select">
                    <option value="all" {{ !request('priority') || request('priority') === 'all' ? 'selected' : '' }}>All Priorities</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="dateFrom">From:</label>
                <input type="date" id="dateFrom" class="filter-input" value="{{ request('date_from') }}">
            </div>
            
            <div class="filter-group">
                <label for="dateTo">To:</label>
                <input type="date" id="dateTo" class="filter-input" value="{{ request('date_to') }}">
            </div>
            
            <button class="btn-primary" onclick="applyFilters()">Apply Filters</button>
            <button class="btn-secondary" onclick="clearFilters()">Clear</button>
        </div>
        
        <div class="bulk-actions">
            <button class="btn-secondary" onclick="markAllAsRead()">Mark All as Read</button>
        </div>
    </div>
    
    <div class="notifications-list">
        @forelse($notifications as $notification)
            <div class="notification-card {{ $notification->is_read ? 'read' : 'unread' }} priority-{{ $notification->priority }}" 
                 id="notification-{{ $notification->id }}"
                 data-id="{{ $notification->id }}">
                <div class="notification-card-header">
                    <div class="notification-title-row">
                        <h3 class="notification-title">{{ $notification->title }}</h3>
                        <div class="notification-badges">
                            @if($notification->priority === 'urgent')
                                <span class="priority-badge urgent">Urgent</span>
                            @elseif($notification->priority === 'high')
                                <span class="priority-badge high">High</span>
                            @endif
                            @if(!$notification->is_read)
                                <span class="unread-badge">Unread</span>
                            @endif
                        </div>
                    </div>
                    <div class="notification-meta">
                        <span class="notification-type">{{ ucfirst(str_replace('_', ' ', $notification->type)) }}</span>
                        <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                
                <div class="notification-message">
                    {{ $notification->message }}
                </div>
                
                @if($notification->data)
                    <div class="notification-data">
                        <strong>Additional Info:</strong>
                        <pre>{{ json_encode($notification->data, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                @endif
                
                <div class="notification-actions">
                    @if($notification->is_read)
                        <button class="btn-small btn-secondary" onclick="markAsUnread({{ $notification->id }})">Mark as Unread</button>
                    @else
                        <button class="btn-small btn-primary" onclick="markAsRead({{ $notification->id }})">Mark as Read</button>
                    @endif
                    <button class="btn-small btn-danger" onclick="deleteNotification({{ $notification->id }})">Delete</button>
                </div>
            </div>
        @empty
            <div class="no-notifications">
                <p>No notifications found.</p>
            </div>
        @endforelse
    </div>
    
    <div class="pagination-wrapper">
        {{ $notifications->links() }}
    </div>
</div>

<style>
.notifications-page {
    padding: 20px;
}

.notifications-header {
    margin-bottom: 30px;
}

.header-actions-panel {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: flex-end;
    margin-bottom: 20px;
    padding: 20px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
}

.filter-select, .filter-input {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.bulk-actions {
    display: flex;
    gap: 10px;
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notification-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #d1d5db;
    transition: all 0.2s ease;
}

.notification-card.unread {
    background: #f0f9ff;
    border-left-color: #347928;
}

.notification-card.priority-urgent {
    border-left-color: #ef4444;
}

.notification-card.priority-high {
    border-left-color: #f59e0b;
}

.notification-card-header {
    margin-bottom: 15px;
}

.notification-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.notification-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.notification-badges {
    display: flex;
    gap: 8px;
}

.priority-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.priority-badge.urgent {
    background: #fee2e2;
    color: #dc2626;
}

.priority-badge.high {
    background: #fef3c7;
    color: #d97706;
}

.unread-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    background: #347928;
    color: white;
}

.notification-meta {
    display: flex;
    gap: 15px;
    font-size: 12px;
    color: #6b7280;
}

.notification-message {
    color: #4b5563;
    line-height: 1.6;
    margin-bottom: 15px;
}

.notification-data {
    background: #f9fafb;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 12px;
}

.notification-data pre {
    margin: 10px 0 0 0;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.notification-actions {
    display: flex;
    gap: 10px;
}

.btn-small {
    padding: 6px 12px;
    font-size: 13px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-small.btn-primary {
    background: #347928;
    color: white;
}

.btn-small.btn-primary:hover {
    background: #1e3a0f;
}

.btn-small.btn-secondary {
    background: #e5e7eb;
    color: #374151;
}

.btn-small.btn-secondary:hover {
    background: #d1d5db;
}

.btn-small.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-small.btn-danger:hover {
    background: #dc2626;
}

.no-notifications {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.pagination-wrapper {
    margin-top: 30px;
    display: flex;
    justify-content: center;
}
</style>

<script>
function applyFilters() {
    const filter = document.getElementById('filterStatus').value;
    const type = document.getElementById('filterType').value;
    const priority = document.getElementById('filterPriority').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    const params = new URLSearchParams();
    if (filter !== 'all') params.append('filter', filter);
    if (type !== 'all') params.append('type', type);
    if (priority !== 'all') params.append('priority', priority);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    window.location.href = '/admin/notifications?' + params.toString();
}

function clearFilters() {
    window.location.href = '/admin/notifications';
}

function markAsRead(id) {
    fetch(`/admin/api/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark notification as read');
    });
}

function markAsUnread(id) {
    fetch(`/admin/api/notifications/${id}/unread`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark notification as unread');
    });
}

function deleteNotification(id) {
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }
    
    fetch(`/admin/api/notifications/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`notification-${id}`).remove();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete notification');
    });
}

function markAllAsRead() {
    if (!confirm('Mark all notifications as read?')) {
        return;
    }
    
    fetch('/admin/api/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to mark all as read');
    });
}

// Highlight notification if specified in URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight');
    if (highlightId) {
        const element = document.getElementById(`notification-${highlightId}`);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            element.style.animation = 'highlight 2s ease';
        }
    }
});

// Add highlight animation
const style = document.createElement('style');
style.textContent = `
    @keyframes highlight {
        0%, 100% { background-color: transparent; }
        50% { background-color: #fef3c7; }
    }
`;
document.head.appendChild(style);
</script>
@endsection

