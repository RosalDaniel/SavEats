<!-- Establishment Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="user-profile">
        <div class="user-avatar">ES</div>
        <div class="user-info">
            <h3>{{ session('user_name', 'Establishment User') }}</h3>
            <p>Establishment</p>
        </div>
    </div>

    <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active" data-page="dashboard">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="listing-management">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                        </svg>
                        Listing Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="order-management">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M7 4V2C7 1.45 7.45 1 8 1h8c.55 0 1 .45 1 1v2h5v2h-2v13c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V6H2V4h5zM9 3v1h6V3H9zM6 6v13h12V6H6z"/>
                        </svg>
                        Order Management
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="announcements">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        Announcements
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="earnings">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                        </svg>
                        Earnings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="donation-hub">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                        Donation Hub
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="impact-reports">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/>
                        </svg>
                        Impact Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="settings">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.82,11.69,4.82,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
                        </svg>
                        Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-page="help">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>
                        </svg>
                        Help Center
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('logout') }}" class="nav-link" onclick="return confirm('Are you sure you want to logout?')">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                        </svg>
                        Logout
                    </a>
                </li>
            </ul>
</nav>
