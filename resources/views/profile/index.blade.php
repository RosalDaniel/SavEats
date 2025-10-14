@extends('layouts.' . $userType)

@section('title', 'Account Profile')
@section('header', 'Account Profile')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@endsection

@section('content')
<div class="profile-container">
    <div class="profile-card">
        <div class="profile-layout">
            <!-- Left Column - Profile Picture -->
            <div class="profile-picture-column">
                <h3>Profile Picture</h3>
                <div class="profile-picture">
                    @if($userData->profile_picture)
                        <img src="{{ Storage::url($userData->profile_picture) }}" alt="Profile Picture" id="profileImage">
                    @else
                        <div class="profile-placeholder">
                            {{ strtoupper(substr($userData->first_name, 0, 1) . substr($userData->last_name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <button class="edit-btn primary" onclick="editProfilePicture()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 640 640">
                        <path fill="#ffffff" d="M160 144C151.2 144 144 151.2 144 160L144 480C144 488.8 151.2 496 160 496L480 496C488.8 496 496 488.8 496 480L496 160C496 151.2 488.8 144 480 144L160 144zM96 160C96 124.7 124.7 96 160 96L480 96C515.3 96 544 124.7 544 160L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160zM224 192C241.7 192 256 206.3 256 224C256 241.7 241.7 256 224 256C206.3 256 192 241.7 192 224C192 206.3 206.3 192 224 192zM360 264C368.5 264 376.4 268.5 380.7 275.8L460.7 411.8C465.1 419.2 465.1 428.4 460.8 435.9C456.5 443.4 448.6 448 440 448L200 448C191.1 448 182.8 443 178.7 435.1C174.6 427.2 175.2 417.6 180.3 410.3L236.3 330.3C240.8 323.9 248.1 320.1 256 320.1C263.9 320.1 271.2 323.9 275.7 330.3L292.9 354.9L339.4 275.9C343.7 268.6 351.6 264.1 360.1 264.1z"/>
                    </svg>
                    Edit Profile
                </button>
            </div>

            <!-- Right Column - Information Sections -->
            <div class="profile-info-column">

        <!-- Personal Information Section -->
        <div class="profile-section">
            <div class="section-header">
                <h3 class="section-title">Personal Information</h3>
            </div>
            <div class="form-group">
                <input type="text" id="firstName" name="first_name" value="{{ $userData->first_name }}" readonly>
            </div>
            <div class="form-group">
                <input type="text" id="lastName" name="last_name" value="{{ $userData->last_name }}" readonly>
            </div>
            @if($userType === 'establishment')
                <div class="form-group">
                    <input type="text" id="businessName" name="business_name" value="{{ $userData->business_name ?? '' }}" readonly>
                </div>
                <div class="form-group file-upload-group">
                    <input type="file" id="birFileUpload" name="bir_file" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                    @if($userData->bir_file)
                        <div class="file-upload-display file-uploaded" onclick="document.getElementById('birFileUpload').click()">
                            <div class="file-info">
                                <svg class="file-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                                <span class="file-upload-text">{{ basename($userData->bir_file) }}</span>
                            </div>
                            <svg class="file-upload-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 14l5-5 5 5z"/>
                            </svg>
                        </div>
                    @else
                        <div class="file-upload-display" onclick="document.getElementById('birFileUpload').click()">
                            <span class="file-upload-text">Upload BIR Certificate</span>
                            <svg class="file-upload-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 14l5-5 5 5z"/>
                            </svg>
                        </div>
                    @endif
                </div>
            @elseif($userType === 'foodbank')
                <div class="form-group">
                    <input type="text" id="organizationName" name="organization_name" value="{{ $userData->organization_name ?? '' }}" readonly>
                </div>
            @else
                <div class="form-group">
                    <input type="text" id="middleName" name="middle_name" value="{{ $userData->middle_name }}" readonly>
                </div>
            @endif
        </div>

        <!-- Contact Information Section -->
        <div class="profile-section">
            <div class="section-header">
                <h3 class="section-title">Contact Information</h3>
                <button class="edit-btn secondary" onclick="openContactModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                    </svg>
                </button>
            </div>
            <div class="form-group">
                <input type="text" id="address" name="address" value="{{ $userData->address }}" placeholder="Address" readonly>
            </div>
            <div class="form-group">
                <input type="tel" id="phone" name="phone" value="{{ $userData->phone }}" placeholder="Phone Number" readonly>
            </div>
            <div class="form-group">
                <input type="email" id="email" name="email" value="{{ $userData->email }}" placeholder="Email Address" readonly>
            </div>
        </div>

        <!-- Account Information Section -->
        <div class="profile-section">
            <div class="section-header">
                <h3 class="section-title">Account Information</h3>
                <button class="edit-btn secondary" onclick="openAccountModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                    </svg>
                </button>
            </div>
            <div class="form-group">
                <input type="text" id="username" name="username" value="{{ $userData->username }}" placeholder="Username" readonly>
            </div>
            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password" readonly>
            </div>
            <div class="form-group">
                <input type="password" id="passwordConfirmation" name="password_confirmation" placeholder="Confirm Password" readonly>
            </div>
        </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden file input for profile picture -->
<input type="file" id="profilePictureInput" accept="image/*" style="display: none;" onchange="handleProfilePictureChange(event)">

<!-- Edit Profile Modal -->
<div class="modal-overlay" id="editProfileModal">
    <div class="modal-content edit-profile-modal">
        <div class="modal-header">
            <h2 class="modal-title">EDIT PROFILE</h2>
        </div>
        <div class="modal-body">
            <div class="profile-picture-upload">
                <div class="profile-picture-frame">
                    <div class="profile-picture-preview" id="profilePicturePreview">
                        @if($userData->profile_picture)
                            <img src="{{ Storage::url($userData->profile_picture) }}" alt="Profile Picture" id="previewImage">
                        @else
                            <div class="profile-placeholder-preview">
                                {{ strtoupper(substr($userData->first_name, 0, 1) . substr($userData->last_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
                <p class="upload-instruction">Drag the frame to adjust the portrait.</p>
                <div class="upload-controls">
                    <button class="upload-photo-btn" onclick="document.getElementById('profilePictureInput').click()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                        </svg>
                        Upload Photo
                    </button>
                    <button class="save-changes-btn" id="saveChangesBtn" onclick="saveProfilePicture()" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Information Modal -->
<div class="modal-overlay" id="contactModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">CONTACT INFORMATION</h2>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <input type="text" id="modalAddress" name="address" placeholder="Enter Address" value="{{ $userData->address }}">
            </div>
            <div class="form-group">
                <input type="tel" id="modalPhone" name="phone" placeholder="Enter Phone Number" value="{{ $userData->phone }}">
            </div>
            <div class="form-group">
                <input type="email" id="modalEmail" name="email" placeholder="Enter Email Address" value="{{ $userData->email }}">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-cancel" onclick="closeContactModal()">Cancel</button>
            <button class="btn btn-confirm" onclick="saveContactInfo()">Confirm</button>
        </div>
    </div>
</div>

<!-- Account Information Modal -->
<div class="modal-overlay" id="accountModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">ACCOUNT INFORMATION</h2>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <input type="password" id="modalPassword" name="password" placeholder="Enter Password">
            </div>
            <div class="form-group">
                <input type="password" id="modalPasswordConfirmation" name="password_confirmation" placeholder="Enter Confirm Password">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-cancel" onclick="closeAccountModal()">Cancel</button>
            <button class="btn btn-confirm" onclick="saveAccountInfo()">Confirm</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/profile.js') }}"></script>
@endsection
