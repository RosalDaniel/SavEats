@extends('layouts.establishment')

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
        <div class="nav-item" data-tab="business">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10z"/>
            </svg>
            <span>Business</span>
        </div>
        <div class="nav-item" data-tab="notifications">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
            </svg>
            <span>Notifications</span>
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
                        <label>Business Name</label>
                        <span>{{ $userData->business_name ?? 'Not provided' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Owner Name</label>
                        <span>{{ $userData->owner_fname }} {{ $userData->owner_lname }}</span>
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
                        <label>Business Type</label>
                        <span>{{ $userData->business_type ?? 'Not specified' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Member Since</label>
                        <span>{{ $userData->created_at->format('F j, Y') }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Business Settings -->
        <div class="settings-section" id="business">
            <div class="section-header">
                <h2>Business Settings</h2>
                <p>Manage your business information and operations</p>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Business Information</h3>
                    <button class="edit-btn" onclick="editBusiness()">Edit</button>
                </div>
                <div class="card-content">
                    <div class="info-row">
                        <label>Account Verification Status</label>
                        <span>
                            @if($userData->verified ?? false)
                                <span class="verification-badge verified">Verified</span>
                            @else
                                <span class="verification-badge not-verified">Not Verified</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <label>BIR File</label>
                        <span>{{ $userData->bir_file ? 'Uploaded' : 'Not uploaded' }}</span>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Food Listing Settings</h3>
                </div>
                <div class="card-content">
                    <div class="preference-item">
                        <label>Auto-approve Orders</label>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
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
                            <h4>New Orders</h4>
                            <p>Get notified when you receive new orders</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Order Cancellations</h4>
                            <p>Get notified when orders are cancelled</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Low Inventory</h4>
                            <p>Get notified when food items are running low</p>
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
                    <h3>Business Notifications</h3>
                </div>
                <div class="card-content">
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Earnings Updates</h4>
                            <p>Get daily earnings summaries</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Platform Updates</h4>
                            <p>Get notified about new features and updates</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox">
                            <span class="slider"></span>
                        </label>
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
