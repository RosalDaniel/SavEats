@extends('layouts.foodbank')

@section('title', 'Settings | SavEats')

@section('header', 'Settings')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/settings.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
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
                        <label>Member Since</label>
                        <span>{{ $userData->created_at->format('F j, Y') }}</span>
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
