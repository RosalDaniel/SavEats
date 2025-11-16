@extends('layouts.admin')

@section('title', 'Admin Dashboard - SavEats')

@section('header', 'Admin Dashboard')

@section('content')
<!-- Summary Cards Grid -->
<div class="stats-grid">
    <!-- Total Users Card -->
    <div class="stat-card">
        <div class="stat-icon users">
            <svg viewBox="0 0 24 24">
                <path d="M16 7c0-2.76-2.24-5-5-5s-5 2.24-5 5 2.24 5 5 5 5-2.24 5-5zM12 14c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Total Users</h3>
            <p class="stat-number">{{ number_format($totalUsers ?? 0) }}</p>
            <div class="stat-breakdown">
                <span>Consumers: {{ number_format($totalConsumers ?? 0) }}</span>
                <span>Establishments: {{ number_format($totalEstablishments ?? 0) }}</span>
                <span>Food Banks: {{ number_format($totalFoodbanks ?? 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Active Listings Card -->
    <div class="stat-card">
        <div class="stat-icon listings">
            <svg viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Active Listings</h3>
            <p class="stat-number">{{ number_format($totalActiveListings ?? 0) }}</p>
            <span class="stat-change">Out of {{ number_format($totalListings ?? 0) }} total</span>
        </div>
    </div>

    <!-- Total Orders Card -->
    <div class="stat-card">
        <div class="stat-icon orders">
            <svg viewBox="0 0 24 24">
                <path d="M7 18c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Total Orders</h3>
            <p class="stat-number">{{ number_format($totalOrders ?? 0) }}</p>
            <div class="stat-breakdown">
                <span class="status-pending">Pending: {{ number_format($ordersByStatus['pending'] ?? 0) }}</span>
                <span class="status-accepted">Accepted: {{ number_format($ordersByStatus['accepted'] ?? 0) }}</span>
                <span class="status-completed">Completed: {{ number_format($ordersByStatus['completed'] ?? 0) }}</span>
                <span class="status-cancelled">Cancelled: {{ number_format($ordersByStatus['cancelled'] ?? 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Total Donations Card -->
    <div class="stat-card">
        <div class="stat-icon donations">
            <svg viewBox="0 0 24 24">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Total Donations</h3>
            <p class="stat-number">{{ number_format($totalDonations ?? 0) }}</p>
            <div class="stat-breakdown">
                <span class="status-completed">Completed: {{ number_format($completedDonations ?? 0) }}</span>
                <span class="status-pending">Pending: {{ number_format($pendingDonations ?? 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Food Rescued Card -->
    <div class="stat-card">
        <div class="stat-icon rescued">
            <svg viewBox="0 0 24 24">
                <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Food Rescued</h3>
            <p class="stat-number">{{ $foodRescuedFormatted ?? '0' }}</p>
            <div class="stat-breakdown">
                <span>From Orders: {{ number_format($foodRescuedFromOrders ?? 0) }} pcs</span>
                <span>From Donations: {{ number_format($foodRescuedFromDonations ?? 0) }} pcs</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="dashboard-content-grid">
    <!-- Monthly Activity Chart -->
    <div class="content-section chart-section">
        <div class="section-header">
            <h2>Monthly Activity</h2>
            <p class="section-subtitle">Last 6 months overview</p>
        </div>
        <div class="chart-container">
            <canvas id="monthlyActivityChart"></canvas>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="content-section">
        <div class="section-header">
            <h2>Recent Activity</h2>
        </div>
        <div class="activity-list">
            @if(isset($recentEstablishments) && $recentEstablishments->count() > 0)
                @foreach($recentEstablishments->take(3) as $establishment)
                <div class="activity-item">
                    <div class="activity-icon">🏪</div>
                    <div class="activity-content">
                        <h4>New establishment registered</h4>
                        <p>{{ $establishment->business_name ?? 'Establishment' }} joined the platform</p>
                        <span class="activity-time">{{ $establishment->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @endforeach
            @endif
            
            @if(isset($recentConsumers) && $recentConsumers->count() > 0)
                @foreach($recentConsumers->take(2) as $consumer)
                <div class="activity-item">
                    <div class="activity-icon">👤</div>
                    <div class="activity-content">
                        <h4>New consumer registered</h4>
                        <p>{{ $consumer->fname ?? '' }} {{ $consumer->lname ?? '' }} joined the platform</p>
                        <span class="activity-time">{{ $consumer->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @endforeach
            @endif
            
            @if(isset($recentDonations) && $recentDonations->count() > 0)
                @foreach($recentDonations->take(2) as $donation)
                <div class="activity-item">
                    <div class="activity-icon">❤️</div>
                    <div class="activity-content">
                        <h4>Donation completed</h4>
                        <p>{{ $donation->item_name ?? 'Item' }} donated successfully</p>
                        <span class="activity-time">{{ $donation->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @endforeach
            @endif
            
            @if((!isset($recentEstablishments) || $recentEstablishments->count() == 0) && 
                (!isset($recentConsumers) || $recentConsumers->count() == 0) && 
                (!isset($recentDonations) || $recentDonations->count() == 0))
                <div class="activity-item">
                    <div class="activity-content">
                        <p>No recent activity</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- System Overview -->
<div class="content-section">
    <h2>System Overview</h2>
    <div class="overview-grid">
        <div class="overview-item">
            <h4>Platform Health</h4>
            <div class="health-indicator good">Excellent</div>
        </div>
        <div class="overview-item">
            <h4>User Engagement</h4>
            <div class="engagement-bar">
                <div class="engagement-fill" style="width: {{ $engagementPercentage ?? 0 }}%"></div>
            </div>
            <span>{{ $engagementPercentage ?? 0 }}% active users (last 30 days)</span>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = @json($monthlyActivity ?? []);
    
    const ctx = document.getElementById('monthlyActivityChart');
    if (!ctx) return;
    
    const labels = monthlyData.map(item => item.month);
    const usersData = monthlyData.map(item => item.users);
    const ordersData = monthlyData.map(item => item.orders);
    const donationsData = monthlyData.map(item => item.donations);
    const listingsData = monthlyData.map(item => item.listings);
    
    // Detect mobile
    const isMobile = window.innerWidth <= 768;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Orders',
                    data: ordersData,
                    borderColor: 'rgb(255, 205, 86)',
                    backgroundColor: 'rgba(255, 205, 86, 0.1)',
                    tension: 0.4,
                    borderWidth: isMobile ? 1.5 : 2,
                    pointRadius: isMobile ? 3 : 4,
                    pointHoverRadius: isMobile ? 4 : 6
                },
                {
                    label: 'Donations',
                    data: donationsData,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    borderWidth: isMobile ? 1.5 : 2,
                    pointRadius: isMobile ? 3 : 4,
                    pointHoverRadius: isMobile ? 4 : 6
                },
                {
                    label: 'Listings',
                    data: listingsData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    borderWidth: isMobile ? 1.5 : 2,
                    pointRadius: isMobile ? 3 : 4,
                    pointHoverRadius: isMobile ? 4 : 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: isMobile ? 10 : 12,
                        padding: isMobile ? 6 : 8,
                        font: {
                            size: isMobile ? 10 : 12
                        }
                    }
                },
                title: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    intersect: false,
                    titleFont: {
                        size: isMobile ? 11 : 12
                    },
                    bodyFont: {
                        size: isMobile ? 10 : 11
                    },
                    padding: isMobile ? 8 : 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        precision: 0,
                        font: {
                            size: isMobile ? 10 : 12
                        }
                    },
                    grid: {
                        display: true
                    }
                },
                x: {
                    ticks: {
                        maxRotation: isMobile ? 0 : 45,
                        minRotation: isMobile ? 0 : 45,
                        font: {
                            size: isMobile ? 9 : 12
                        }
                    },
                    grid: {
                        display: true
                    }
                }
            },
            interaction: {
                mode: 'index',
                intersect: false
            }
        }
    });
});
</script>
@endpush
@endsection
