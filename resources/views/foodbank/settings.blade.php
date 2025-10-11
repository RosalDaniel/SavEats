@extends('layouts.foodbank')

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
        <div class="nav-item" data-tab="organization">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10z"/>
            </svg>
            <span>Organization</span>
        </div>
        <div class="nav-item" data-tab="donations">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            <span>Donations</span>
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
                        <label>Organization Name</label>
                        <span>{{ $userData->organization_name ?? 'Not provided' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Contact Person</label>
                        <span>{{ $userData->contact_person ?? 'Not provided' }}</span>
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
                        <label>Organization Type</label>
                        <span>{{ $userData->organization_type ?? 'Not specified' }}</span>
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
                            <p>Download a copy of your organization data</p>
                        </div>
                        <button class="action-btn" onclick="downloadData()">Download</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organization Settings -->
        <div class="settings-section" id="organization">
            <div class="section-header">
                <h2>Organization Settings</h2>
                <p>Manage your foodbank organization information</p>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Organization Information</h3>
                    <button class="edit-btn" onclick="editOrganization()">Edit</button>
                </div>
                <div class="card-content">
                    <div class="info-row">
                        <label>Registration Number</label>
                        <span>{{ $userData->registration_number ?? 'Not provided' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Organization Registration</label>
                        <span>{{ $userData->org_registration ?? 'Not provided' }}</span>
                    </div>
                    <div class="info-row">
                        <label>Service Areas</label>
                        <span>Metro Manila, Quezon City, Makati</span>
                    </div>
                    <div class="info-row">
                        <label>Operating Hours</label>
                        <span>8:00 AM - 6:00 PM</span>
                    </div>
                    <div class="info-row">
                        <label>Volunteer Count</label>
                        <span>25 active volunteers</span>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Distribution Settings</h3>
                </div>
                <div class="card-content">
                    <div class="preference-item">
                        <label>Auto-accept Donations</label>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="preference-item">
                        <label>Maximum Collection Distance</label>
                        <input type="range" class="preference-range" min="1" max="50" value="15">
                        <span class="range-value">15 km</span>
                    </div>
                    <div class="preference-item">
                        <label>Collection Time Window</label>
                        <select class="preference-select">
                            <option value="2">2 hours</option>
                            <option value="4">4 hours</option>
                            <option value="6">6 hours</option>
                            <option value="24">24 hours</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Donations Settings -->
        <div class="settings-section" id="donations">
            <div class="section-header">
                <h2>Donation Settings</h2>
                <p>Configure your donation collection preferences</p>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Collection Preferences</h3>
                </div>
                <div class="card-content">
                    <div class="preference-item">
                        <label>Preferred Food Types</label>
                        <div class="checkbox-group">
                            <label class="checkbox-item">
                                <input type="checkbox" checked>
                                <span>Fresh Produce</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" checked>
                                <span>Prepared Meals</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" checked>
                                <span>Baked Goods</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox">
                                <span>Dairy Products</span>
                            </label>
                            <label class="checkbox-item">
                                <input type="checkbox" checked>
                                <span>Non-perishables</span>
                            </label>
                        </div>
                    </div>
                    <div class="preference-item">
                        <label>Minimum Donation Quantity</label>
                        <input type="number" class="preference-input" value="5" min="1">
                        <span class="input-suffix">items</span>
                    </div>
                    <div class="preference-item">
                        <label>Collection Schedule</label>
                        <select class="preference-select">
                            <option value="immediate">Immediate</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="card-header">
                    <h3>Distribution Settings</h3>
                </div>
                <div class="card-content">
                    <div class="preference-item">
                        <label>Distribution Method</label>
                        <select class="preference-select">
                            <option value="pickup">Pickup Only</option>
                            <option value="delivery">Delivery Only</option>
                            <option value="both">Both Pickup & Delivery</option>
                        </select>
                    </div>
                    <div class="preference-item">
                        <label>Distribution Hours</label>
                        <input type="text" class="preference-input" value="9:00 AM - 5:00 PM">
                    </div>
                    <div class="preference-item">
                        <label>Require Registration</label>
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
                    <h3>Donation Notifications</h3>
                </div>
                <div class="card-content">
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>New Donations</h4>
                            <p>Get notified when new donations are available</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Collection Reminders</h4>
                            <p>Get reminded about pending collections</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Distribution Updates</h4>
                            <p>Get notified about distribution activities</p>
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
                    <h3>Impact Notifications</h3>
                </div>
                <div class="card-content">
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Impact Reports</h4>
                            <p>Get weekly impact reports</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="toggle-item">
                        <div class="toggle-info">
                            <h4>Volunteer Updates</h4>
                            <p>Get updates about volunteer activities</p>
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
