// Admin System Logs JavaScript
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    // Load initial logs
    loadLogs(1);
    
    // Initialize filters
    initializeFilters();
    
    // Close export menu when clicking outside
    document.addEventListener('click', function(e) {
        const exportMenu = document.getElementById('exportMenu');
        const exportBtn = document.querySelector('.export-dropdown button');
        if (exportMenu && !exportMenu.contains(e.target) && !exportBtn.contains(e.target)) {
            exportMenu.classList.remove('active');
        }
    });
});

function initializeFilters() {
    const searchInput = document.getElementById('logSearch');
    const eventTypeFilter = document.getElementById('eventTypeFilter');
    const severityFilter = document.getElementById('severityFilter');
    const userTypeFilter = document.getElementById('userTypeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => loadLogs(1), 300);
        });
    }
    
    if (eventTypeFilter) {
        eventTypeFilter.addEventListener('change', () => loadLogs(1));
    }
    
    if (severityFilter) {
        severityFilter.addEventListener('change', () => loadLogs(1));
    }
    
    if (userTypeFilter) {
        userTypeFilter.addEventListener('change', () => loadLogs(1));
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', () => loadLogs(1));
    }
    
    if (dateFrom) {
        dateFrom.addEventListener('change', () => loadLogs(1));
    }
    
    if (dateTo) {
        dateTo.addEventListener('change', () => loadLogs(1));
    }
}

function loadLogs(page = 1) {
    currentPage = page;
    
    const search = document.getElementById('logSearch')?.value || '';
    const eventType = document.getElementById('eventTypeFilter')?.value || '';
    const severity = document.getElementById('severityFilter')?.value || '';
    const userType = document.getElementById('userTypeFilter')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const dateFrom = document.getElementById('dateFrom')?.value || '';
    const dateTo = document.getElementById('dateTo')?.value || '';
    
    const params = new URLSearchParams({
        page: page,
        ...(search && { search }),
        ...(eventType && { event_type: eventType }),
        ...(severity && { severity }),
        ...(userType && { user_type: userType }),
        ...(status && { status }),
        ...(dateFrom && { date_from: dateFrom }),
        ...(dateTo && { date_to: dateTo })
    });
    
    fetch(`${SYSTEM_LOGS_ROUTES.data}?${params}`, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderLogsTable(data.data);
            renderPagination(data.data);
        }
    })
    .catch(error => {
        console.error('Error loading logs:', error);
        showNotification('Error loading logs', 'error');
    });
}

function renderLogsTable(data) {
    const tbody = document.getElementById('logsTableBody');
    if (!tbody) return;
    
    if (data.data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="loading">No logs found</td></tr>';
        return;
    }
    
    tbody.innerHTML = data.data.map(log => {
        const timestamp = new Date(log.created_at);
        const userInfo = log.user_email 
            ? `${log.user_email}${log.user_type ? ' (' + log.user_type + ')' : ''}`
            : (log.user_type || 'N/A');
        
        return `
            <tr>
                <td>#${log.id}</td>
                <td>${formatDateTime(timestamp)}</td>
                <td><span class="event-type-badge">${formatEventType(log.event_type)}</span></td>
                <td><span class="severity-badge ${log.severity}">${log.severity}</span></td>
                <td>${escapeHtml(userInfo)}</td>
                <td>${log.ip_address || '-'}</td>
                <td>${log.action ? escapeHtml(log.action) : '-'}</td>
                <td><span class="status-badge ${log.status}">${log.status}</span></td>
                <td>${log.description ? escapeHtml(log.description.substring(0, 100)) + (log.description.length > 100 ? '...' : '') : '-'}</td>
            </tr>
        `;
    }).join('');
}

function renderPagination(data) {
    const container = document.getElementById('logsPagination');
    if (!container || !data) return;
    
    const currentPage = data.current_page || 1;
    const lastPage = data.last_page || 1;
    
    if (lastPage <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="pagination-info">';
    html += `Page ${currentPage} of ${lastPage} (${data.total || 0} total)`;
    html += '</div>';
    html += '<div style="display: flex; gap: 0.5rem;">';
    
    // Previous button
    html += `<button class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="loadLogs(${currentPage - 1})">Previous</button>`;
    
    // Page numbers
    for (let i = 1; i <= lastPage; i++) {
        if (i === 1 || i === lastPage || (i >= currentPage - 2 && i <= currentPage + 2)) {
            html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="loadLogs(${i})">${i}</button>`;
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            html += '<span class="pagination-info">...</span>';
        }
    }
    
    // Next button
    html += `<button class="pagination-btn" ${currentPage === lastPage ? 'disabled' : ''} onclick="loadLogs(${currentPage + 1})">Next</button>`;
    html += '</div>';
    
    container.innerHTML = html;
}

function clearFilters() {
    document.getElementById('logSearch').value = '';
    document.getElementById('eventTypeFilter').value = '';
    document.getElementById('severityFilter').value = '';
    document.getElementById('userTypeFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    loadLogs(1);
}

function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    if (menu) {
        menu.classList.toggle('active');
    }
}

function exportLogs(format) {
    const search = document.getElementById('logSearch')?.value || '';
    const eventType = document.getElementById('eventTypeFilter')?.value || '';
    const severity = document.getElementById('severityFilter')?.value || '';
    const userType = document.getElementById('userTypeFilter')?.value || '';
    const status = document.getElementById('statusFilter')?.value || '';
    const dateFrom = document.getElementById('dateFrom')?.value || '';
    const dateTo = document.getElementById('dateTo')?.value || '';
    
    const params = new URLSearchParams({
        ...(search && { search }),
        ...(eventType && { event_type: eventType }),
        ...(severity && { severity }),
        ...(userType && { user_type: userType }),
        ...(status && { status }),
        ...(dateFrom && { date_from: dateFrom }),
        ...(dateTo && { date_to: dateTo })
    });
    
    let route;
    switch(format) {
        case 'csv':
            route = SYSTEM_LOGS_ROUTES.exportCsv;
            break;
        case 'pdf':
            route = SYSTEM_LOGS_ROUTES.exportPdf;
            break;
        case 'excel':
            route = SYSTEM_LOGS_ROUTES.exportExcel;
            break;
        default:
            return;
    }
    
    window.location.href = `${route}?${params}`;
    toggleExportMenu();
}

function formatDateTime(date) {
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatEventType(eventType) {
    return eventType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    // Simple notification - you can enhance this with a toast library
    alert(message);
}

