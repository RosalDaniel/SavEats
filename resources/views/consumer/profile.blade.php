@extends('layouts.consumer')

@section('title', 'My Profile | SavEats')

@section('header', 'My Profile')

@section('content')
<div class="profile-management">
    <div class="profile-header">
        <div class="profile-avatar-section">
            <div class="profile-avatar large">
                {{ substr(session('user_name', 'U'), 0, 2) }}
            </div>
            <button class="btn-secondary">Change Photo</button>
        </div>
        <div class="profile-info">
            <h2>{{ session('user_name', 'User Name') }}</h2>
            <p class="profile-role">Consumer</p>
            <p class="profile-joined">Member since January 2024</p>
        </div>
    </div>

    <div class="profile-content">
        <div class="profile-section">
            <h3>Personal Information</h3>
            <form class="profile-form">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ session('user_name', '') }}" class="form-input">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ session('user_email', '') }}" class="form-input">
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="" class="form-input">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" class="form-textarea" rows="3"></textarea>
                </div>
                <button type="submit" class="btn-primary">Update Information</button>
            </form>
        </div>

        <div class="profile-section">
            <h3>Preferences</h3>
            <form class="preferences-form">
                <div class="form-group">
                    <label>Food Categories (Select your preferences)</label>
                    <div class="checkbox-group">
                        <label class="checkbox-item">
                            <input type="checkbox" name="categories[]" value="vegetables">
                            <span class="checkbox-label">Vegetables</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="categories[]" value="fruits">
                            <span class="checkbox-label">Fruits</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="categories[]" value="bakery">
                            <span class="checkbox-label">Bakery Items</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox" name="categories[]" value="dairy">
                            <span class="checkbox-label">Dairy Products</span>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pickup-radius">Pickup Radius (km)</label>
                    <select id="pickup-radius" name="pickup_radius" class="form-select">
                        <option value="5">5 km</option>
                        <option value="10">10 km</option>
                        <option value="15">15 km</option>
                        <option value="20">20 km</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Save Preferences</button>
            </form>
        </div>

        <div class="profile-section">
            <h3>Notifications</h3>
            <form class="notifications-form">
                <div class="notification-item">
                    <label class="switch">
                        <input type="checkbox" name="notifications[]" value="new-listings">
                        <span class="slider"></span>
                    </label>
                    <div class="notification-info">
                        <h4>New Listings</h4>
                        <p>Get notified when new food items are available near you</p>
                    </div>
                </div>
                <div class="notification-item">
                    <label class="switch">
                        <input type="checkbox" name="notifications[]" value="price-drops">
                        <span class="slider"></span>
                    </label>
                    <div class="notification-info">
                        <h4>Price Drops</h4>
                        <p>Alert me when prices drop on items I'm interested in</p>
                    </div>
                </div>
                <div class="notification-item">
                    <label class="switch">
                        <input type="checkbox" name="notifications[]" value="order-updates">
                        <span class="slider"></span>
                    </label>
                    <div class="notification-info">
                        <h4>Order Updates</h4>
                        <p>Receive updates about my order status</p>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Update Notifications</button>
            </form>
        </div>

        <div class="profile-section">
            <h3>Account Security</h3>
            <div class="security-actions">
                <button class="btn-secondary">Change Password</button>
                <button class="btn-secondary">Enable Two-Factor Authentication</button>
                <button class="btn-danger">Delete Account</button>
            </div>
        </div>
    </div>
</div>
@endsection
