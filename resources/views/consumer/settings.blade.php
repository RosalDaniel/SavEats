@extends('layouts.consumer')

@section('title', 'Settings | SavEats')

@section('header', 'Settings')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/settings.css') }}">
@endsection

@section('content')
<div class="settings-container">
    <!-- Settings Navigation -->
    <div class="settings-nav">
        <div class="nav-item active" data-tab="account">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
            <span>Account</span>
        </div>
        <div class="nav-item" data-tab="notifications">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
            </svg>
            <span>Notifications</span>
        </div>
        <div class="nav-item" data-tab="privacy">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM8.9 6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2H8.9V6z"/>
            </svg>
            <span>Privacy</span>
        </div>
        <div class="nav-item" data-tab="preferences">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.82,11.69,4.82,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z"/>
            </svg>
            <span>Preferences</span>
        </div>
        <div class="nav-item" data-tab="security">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7C13.4,7 14.8,8.6 14.8,10V11.5C15.4,11.5 16,12.4 16,13V16C16,16.6 15.6,17 15,17H9C8.4,17 8,16.6 8,16V13C8,12.4 8.4,11.5 9,11.5V10C9,8.6 10.6,7 12,7M12,8.2C11.2,8.2 10.2,9.2 10.2,10V11.5H13.8V10C13.8,9.2 12.8,8.2 12,8.2Z"/>
            </svg>
            <span>Security</span>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
        <!-- Account Settings -->
        <div class="settings-section active" id="account">
            <div class="section-header">
                <h2>Account Settings</h2>
                <p>Manage your account information and profile</p>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Profile Information</h3>
                    <button class="edit-btn" onclick="editProfile()">Edit</button>
                </div>
                <div class="card-content">
                    <div class="info-row">
                        <label>Full Name</label>
                        <span>{{ $userData->first_name }} {{ $userData->last_name }}</span>
                    </div>
                    <div class="info-row">
                        <label>Email</label>
                        <span>{{ $userData->email }}</span>
                    </div>
                    <div class="info-row">
                        <label>Phone</label>
                        <span>{{ $userData->phone_no ?? 'Not provided' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Address</label>
                        <span>{{ $userData->address ?? 'Not provided' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Member Since</label>
                        <span>{{ $userData->created_at->format('F j, Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Account Actions</h3>
                </div>
                <div class="card-content">
                    <div class="action-item">
                        <div class="action-info">
                            <h4>Change Password</h4>
                            <p>Update your account password</p>
                        </div>
                        <button class="action-btn" onclick="changePassword()">Change</button>
                    </div>
                    <div class="action-item">
                        <div class="action-info">
                            <h4>Download Data</h4>
                            <p>Download a copy of your data</p>
                        </div>
                        <button class="action-btn" onclick="downloadData()">Download</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications Settings -->
        <div class="settings-section" id="notifications">
            <div class="section-header">
                <h2>Notification Settings</h2>
                <p>Choose how you want to be notified</p>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Order Notifications</h3>
                </div>
                <div class="card-content">
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Order Confirmations</h4>
                            <p>Get notified when your order is confirmed</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Order Updates</h4>
                            <p>Get notified about order status changes</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Delivery Notifications</h4>
                            <p>Get notified when your order is ready for pickup/delivery</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Marketing Notifications</h3>
                </div>
                <div class="card-content">
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Promotional Offers</h4>
                            <p>Receive special offers and discounts</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>New Listings</h4>
                            <p>Get notified about new food listings in your area</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy Settings -->
        <div class="settings-section" id="privacy">
            <div class="section-header">
                <h2>Privacy Settings</h2>
                <p>Control your privacy and data sharing</p>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Data Sharing</h3>
                </div>
                <div class="card-content">
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Location Services</h4>
                            <p>Allow SavEats to use your location for better recommendations</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Analytics</h4>
                            <p>Help improve our service by sharing anonymous usage data</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Account Visibility</h3>
                </div>
                <div class="card-content">
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Profile Visibility</h4>
                            <p>Make your profile visible to other users</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preferences Settings -->
        <div class="settings-section" id="preferences">
            <div class="section-header">
                <h2>Preferences</h2>
                <p>Customize your SavEats experience</p>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Display Preferences</h3>
                </div>
                <div class="card-content">
                    <div class="preference-item">
                        <label>Theme</label>
                        <select class="preference-select">
                            <option value="light">Light</option>
                            <option value="dark">Dark</option>
                            <option value="auto">Auto</option>
                        </select>
                    </div>
                    <div class="preference-item">
                        <label>Language</label>
                        <select class="preference-select">
                            <option value="en">English</option>
                            <option value="fil">Filipino</option>
                        </select>
                    </div>
                    <div class="preference-item">
                        <label>Currency</label>
                        <select class="preference-select">
                            <option value="php">PHP (₱)</option>
                            <option value="usd">USD ($)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Food Preferences</h3>
                </div>
                <div class="card-content">
                    <div class="preference-item">
                        <label>Default Sort By</label>
                        <select class="preference-select">
                            <option value="newest">Newest First</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="distance">Distance</option>
                        </select>
                    </div>
                    <div class="preference-item">
                        <label>Max Distance (km)</label>
                        <input type="range" class="preference-range" min="1" max="50" value="10">
                        <span class="range-value">10 km</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="settings-section" id="security">
            <div class="section-header">
                <h2>Security Settings</h2>
                <p>Manage your account security</p>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Login Security</h3>
                </div>
                <div class="card-content">
                    <div class="info-row">
                        <label>Last Login</label>
                        <span>{{ now()->format('F j, Y \a\t g:i A') }}</span>
                    </div>
                    <div class="info-row">
                        <label>Login Method</label>
                        <span>Email & Password</span>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Security Actions</h3>
                </div>
                <div class="card-content">
                    <div class="action-item">
                        <div class="action-info">
                            <h4>Change Password</h4>
                            <p>Update your account password</p>
                        </div>
                        <button class="action-btn" onclick="changePassword()">Change</button>
                    </div>
                    <div class="action-item">
                        <div class="action-info">
                            <h4>Two-Factor Authentication</h4>
                            <p>Add an extra layer of security</p>
                        </div>
                        <button class="action-btn secondary" onclick="enable2FA()">Enable</button>
                    </div>
                    <div class="action-item">
                        <div class="action-info">
                            <h4>Active Sessions</h4>
                            <p>Manage your active login sessions</p>
                        </div>
                        <button class="action-btn" onclick="viewSessions()">View</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal-overlay" id="passwordModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button class="close-btn" onclick="closePasswordModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="passwordForm">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" id="currentPassword" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="newPassword" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" id="confirmPassword" required>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" onclick="closePasswordModal()">Cancel</button>
            <button class="btn-confirm" onclick="savePassword()">Save Changes</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/settings.js') }}"></script>
@endsection
