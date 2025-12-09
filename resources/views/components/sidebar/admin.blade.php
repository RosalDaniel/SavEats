@php use Illuminate\Support\Facades\Storage; @endphp
<!-- Admin Sidebar -->
<nav class="sidebar admin-sidebar" id="sidebar">
    <div class="user-profile">
        <div class="user-avatar">
            {{ substr(session('user_name', 'Admin'), 0, 2) }}
        </div>
        <div class="user-info">
            <h3>{{ session('user_name', 'Admin User') }}</h3>
            <p>Administrator</p>
        </div>
    </div>

    <ul class="nav-menu">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') || request()->routeIs('dashboard.admin') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.cms') }}" class="nav-link {{ request()->routeIs('admin.cms*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" viewBox="0 0 640 640">
                    <path fill="#000000" d="M384 224L480 224L480 160L384 160L384 224zM96 224L96 144C96 117.5 117.5 96 144 96L496 96C522.5 96 544 117.5 544 144L544 240C544 266.5 522.5 288 496 288L144 288C117.5 288 96 266.5 96 240L96 224zM256 480L480 480L480 416L256 416L256 480zM96 480L96 400C96 373.5 117.5 352 144 352L496 352C522.5 352 544 373.5 544 400L544 496C544 522.5 522.5 544 496 544L144 544C117.5 544 96 522.5 96 496L96 480z"/>
                </svg>
                Content Management
            </a>
        </li>
        <li class="nav-item nav-item-has-children {{ request()->routeIs('admin.users') || request()->routeIs('admin.deletion-requests') ? 'expanded' : '' }}">
            <a href="#" class="nav-link nav-link-parent {{ request()->routeIs('admin.users') || request()->routeIs('admin.deletion-requests') ? 'active' : '' }}" onclick="toggleSubmenu(event, this)">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M16 7c0-2.76-2.24-5-5-5s-5 2.24-5 5 2.24 5 5 5 5-2.24 5-5zM12 14c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z"/>
                </svg>
                <span>User Management</span>
                <svg class="nav-arrow" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </a>
            <ul class="nav-submenu {{ request()->routeIs('admin.users') || request()->routeIs('admin.deletion-requests') ? 'expanded' : '' }}">
                <li class="nav-subitem">
                    <a href="{{ route('admin.users') }}" class="nav-link nav-link-child {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M16 7c0-2.76-2.24-5-5-5s-5 2.24-5 5 2.24 5 5 5 5-2.24 5-5zM12 14c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z"/>
                        </svg>
                        Manage Users
                    </a>
                </li>
                <li class="nav-subitem">
                    <a href="{{ route('admin.deletion-requests') }}" class="nav-link nav-link-child {{ request()->routeIs('admin.deletion-requests') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        Deletion Requests
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item nav-item-has-children {{ request()->routeIs('admin.establishments') || request()->routeIs('admin.food-listings') || request()->routeIs('admin.orders') ? 'expanded' : '' }}">
            <a href="#" class="nav-link nav-link-parent {{ request()->routeIs('admin.establishments') || request()->routeIs('admin.food-listings') || request()->routeIs('admin.orders') ? 'active' : '' }}" onclick="toggleSubmenu(event, this)">
                <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" viewBox="0 0 640 640">
                    <path fill="#000000" d="M94.7 136.3C101.6 112.4 123.5 96 148.4 96L492.4 96C517.3 96 539.2 112.4 546.2 136.3L569.6 216.5C582.4 260.2 549.5 304 504 304C477.7 304 454.6 289.1 443.2 266.9C431.6 288.8 408.6 304 381.8 304C355.2 304 332.1 289 320.5 267C308.9 289 285.8 304 259.2 304C232.4 304 209.4 288.9 197.8 266.9C186.4 289 163.3 304 137 304C91.4 304 58.6 260.3 71.4 216.5L94.7 136.3zM160.4 416L480.4 416L480.4 349.6C488 351.2 495.9 352 503.9 352C518.2 352 531.9 349.4 544.4 344.8L544.4 496C544.4 522.5 522.9 544 496.4 544L144.4 544C117.9 544 96.4 522.5 96.4 496L96.4 344.8C108.9 349.4 122.5 352 136.9 352C145 352 152.8 351.2 160.4 349.6L160.4 416z"/>
                </svg>
                <span>Establishments</span>
                <svg class="nav-arrow" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </a>
            <ul class="nav-submenu {{ request()->routeIs('admin.establishments') || request()->routeIs('admin.food-listings') || request()->routeIs('admin.orders') ? 'expanded' : '' }}">
                <li class="nav-subitem">
                    <a href="{{ route('admin.establishments') }}" class="nav-link nav-link-child {{ request()->routeIs('admin.establishments') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" viewBox="0 0 640 640">
                            <path fill="#000000" d="M94.7 136.3C101.6 112.4 123.5 96 148.4 96L492.4 96C517.3 96 539.2 112.4 546.2 136.3L569.6 216.5C582.4 260.2 549.5 304 504 304C477.7 304 454.6 289.1 443.2 266.9C431.6 288.8 408.6 304 381.8 304C355.2 304 332.1 289 320.5 267C308.9 289 285.8 304 259.2 304C232.4 304 209.4 288.9 197.8 266.9C186.4 289 163.3 304 137 304C91.4 304 58.6 260.3 71.4 216.5L94.7 136.3zM160.4 416L480.4 416L480.4 349.6C488 351.2 495.9 352 503.9 352C518.2 352 531.9 349.4 544.4 344.8L544.4 496C544.4 522.5 522.9 544 496.4 544L144.4 544C117.9 544 96.4 522.5 96.4 496L96.4 344.8C108.9 349.4 122.5 352 136.9 352C145 352 152.8 351.2 160.4 349.6L160.4 416z"/>
                        </svg>
                        Manage Establishments
                    </a>
                </li>
                <li class="nav-subitem">
                    <a href="{{ route('admin.food-listings') }}" class="nav-link nav-link-child {{ request()->routeIs('admin.food-listings') ? 'active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" viewBox="0 0 640 640">
                            <path fill="#000000" d="M104 112C90.7 112 80 122.7 80 136L80 184C80 197.3 90.7 208 104 208L152 208C165.3 208 176 197.3 176 184L176 136C176 122.7 165.3 112 152 112L104 112zM256 128C238.3 128 224 142.3 224 160C224 177.7 238.3 192 256 192L544 192C561.7 192 576 177.7 576 160C576 142.3 561.7 128 544 128L256 128zM256 288C238.3 288 224 302.3 224 320C224 337.7 238.3 352 256 352L544 352C561.7 352 576 337.7 576 320C576 302.3 561.7 288 544 288L256 288zM256 448C238.3 448 224 462.3 224 480C224 497.7 238.3 512 256 512L544 512C561.7 512 576 497.7 576 480C576 462.3 561.7 448 544 448L256 448zM80 296L80 344C80 357.3 90.7 368 104 368L152 368C165.3 368 176 357.3 176 344L176 296C176 282.7 165.3 272 152 272L104 272C90.7 272 80 282.7 80 296zM104 432C90.7 432 80 442.7 80 456L80 504C80 517.3 90.7 528 104 528L152 528C165.3 528 176 517.3 176 504L176 456C176 442.7 165.3 432 152 432L104 432z"/>
                        </svg>
                        Food Listings
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item nav-item-has-children {{ request()->routeIs('admin.foodbanks') || request()->routeIs('admin.foodbanks.details') || request()->routeIs('admin.donations') ? 'expanded' : '' }}">
            <a href="#" class="nav-link nav-link-parent {{ request()->routeIs('admin.foodbanks') || request()->routeIs('admin.foodbanks.details') || request()->routeIs('admin.donations') ? 'active' : '' }}" onclick="toggleSubmenu(event, this)">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span>Food Bank</span>
                <svg class="nav-arrow" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                </svg>
            </a>
            <ul class="nav-submenu {{ request()->routeIs('admin.foodbanks') || request()->routeIs('admin.foodbanks.details') || request()->routeIs('admin.donations') ? 'expanded' : '' }}">
                <li class="nav-subitem">
                    <a href="{{ route('admin.foodbanks') }}" class="nav-link nav-link-child {{ request()->routeIs('admin.foodbanks') || request()->routeIs('admin.foodbanks.details') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        Manage Food Bank
                    </a>
                </li>
                <li class="nav-subitem">
                    <a href="{{ route('admin.donations') }}" class="nav-link nav-link-child {{ request()->routeIs('admin.donations') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                        </svg>
                        Donation Hub
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.orders') }}" class="nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" viewBox="0 0 640 640">
                    <path fill="#000000" d="M24 48C10.7 48 0 58.7 0 72C0 85.3 10.7 96 24 96L69.3 96C73.2 96 76.5 98.8 77.2 102.6L129.3 388.9C135.5 423.1 165.3 448 200.1 448L456 448C469.3 448 480 437.3 480 424C480 410.7 469.3 400 456 400L200.1 400C188.5 400 178.6 391.7 176.5 380.3L171.4 352L475 352C505.8 352 532.2 330.1 537.9 299.8L568.9 133.9C572.6 114.2 557.5 96 537.4 96L124.7 96L124.3 94C119.5 67.4 96.3 48 69.2 48L24 48zM208 576C234.5 576 256 554.5 256 528C256 501.5 234.5 480 208 480C181.5 480 160 501.5 160 528C160 554.5 181.5 576 208 576zM432 576C458.5 576 480 554.5 480 528C480 501.5 458.5 480 432 480C405.5 480 384 501.5 384 528C384 554.5 405.5 576 432 576z"/>
                </svg>
                Orders
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.reviews') }}" class="nav-link {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" viewBox="0 0 640 640">
                <path fill="#000000" d="M480 272C480 317.9 465.1 360.3 440 394.7L566.6 521.4C579.1 533.9 579.1 554.2 566.6 566.7C554.1 579.2 533.8 579.2 521.3 566.7L394.7 440C360.3 465.1 317.9 480 272 480C157.1 480 64 386.9 64 272C64 157.1 157.1 64 272 64C386.9 64 480 157.1 480 272zM272 416C351.5 416 416 351.5 416 272C416 192.5 351.5 128 272 128C192.5 128 128 192.5 128 272C128 351.5 192.5 416 272 416z"/>
                </svg>
                Review Management
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.earnings') }}" class="nav-link {{ request()->routeIs('admin.earnings*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/>
                </svg>
                SavEats Earnings
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.system-logs') }}" class="nav-link {{ request()->routeIs('admin.system-logs*') ? 'active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                </svg>
                System Logs
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('logout') }}" class="nav-link">
                <svg class="nav-icon" viewBox="0 0 24 24">
                    <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                </svg>
                Logout
            </a>
        </li>
    </ul>
</nav>
