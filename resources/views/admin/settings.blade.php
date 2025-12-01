@extends('layouts.admin')

@section('title', 'System Settings - Admin Dashboard')

@section('header', 'System Settings')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin-settings.css') }}">
@endsection

@section('content')
<div class="settings-page">
    <div class="settings-container">
        <!-- General Settings -->
        <div class="settings-panel" data-group="general">
            <div class="panel-header">
                <h2>
                    <svg class="panel-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    General Settings
                </h2>
                <p class="panel-description">Configure basic platform information and branding</p>
            </div>
            <div class="panel-body">
                <form id="general-settings-form" class="settings-form">
                    <div class="form-group">
                        <label for="site_name">Site Name</label>
                        <input type="text" id="site_name" name="site_name" class="form-control" 
                               value="{{ $settingsArray['site_name'] ?? 'SavEats' }}" 
                               placeholder="Enter site name">
                    </div>

                    <div class="form-group">
                        <label for="system_description">System Description</label>
                        <textarea id="system_description" name="system_description" class="form-control" 
                                  rows="3" placeholder="Enter system description">{{ $settingsArray['system_description'] ?? '' }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="platform_logo">Platform Logo</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="platform_logo" name="platform_logo" 
                                   accept="image/*" class="file-input">
                            <div class="file-upload-display">
                                @if(isset($settingsArray['platform_logo']) && $settingsArray['platform_logo'])
                                    <img src="{{ $settingsArray['platform_logo'] }}" alt="Current Logo" class="current-logo">
                                @else
                                    <div class="no-file">No logo uploaded</div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('platform_logo').click()">
                                Choose File
                            </button>
                        </div>
                        <small class="form-text">Recommended: PNG or SVG, max 2MB</small>
                    </div>

                    <div class="form-group">
                        <label for="favicon">Favicon</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="favicon" name="favicon" 
                                   accept="image/*,.ico" class="file-input">
                            <div class="file-upload-display">
                                @if(isset($settingsArray['favicon']) && $settingsArray['favicon'])
                                    <img src="{{ $settingsArray['favicon'] }}" alt="Current Favicon" class="current-favicon">
                                @else
                                    <div class="no-file">No favicon uploaded</div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('favicon').click()">
                                Choose File
                            </button>
                        </div>
                        <small class="form-text">Recommended: ICO or PNG, max 512KB</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" onclick="resetSettings('general')">Reset to Defaults</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Authentication Settings -->
        <div class="settings-panel" data-group="auth">
            <div class="panel-header">
                <h2>
                    <svg class="panel-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg>
                    Authentication Settings
                </h2>
                <p class="panel-description">Configure password policies and session management</p>
            </div>
            <div class="panel-body">
                <form id="auth-settings-form" class="settings-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password_min_length">Minimum Password Length</label>
                            <input type="number" id="password_min_length" name="password_min_length" 
                                   class="form-control" min="6" max="32"
                                   value="{{ $settingsArray['password_min_length'] ?? '8' }}">
                        </div>

                        <div class="form-group">
                            <label for="session_timeout">Session Timeout (minutes)</label>
                            <input type="number" id="session_timeout" name="session_timeout" 
                                   class="form-control" min="5" max="1440"
                                   value="{{ $settingsArray['session_timeout'] ?? '120' }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="password_require_uppercase" value="1"
                                   {{ isset($settingsArray['password_require_uppercase']) && $settingsArray['password_require_uppercase'] == '1' ? 'checked' : '' }}>
                            <span>Require Uppercase Letters</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="password_require_lowercase" value="1"
                                   {{ isset($settingsArray['password_require_lowercase']) && $settingsArray['password_require_lowercase'] == '1' ? 'checked' : '' }}>
                            <span>Require Lowercase Letters</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="password_require_numbers" value="1"
                                   {{ isset($settingsArray['password_require_numbers']) && $settingsArray['password_require_numbers'] == '1' ? 'checked' : '' }}>
                            <span>Require Numbers</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="password_require_symbols" value="1"
                                   {{ isset($settingsArray['password_require_symbols']) && $settingsArray['password_require_symbols'] == '1' ? 'checked' : '' }}>
                            <span>Require Special Characters</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="multi_login_allowed" value="1"
                                   {{ isset($settingsArray['multi_login_allowed']) && $settingsArray['multi_login_allowed'] == '1' ? 'checked' : '' }}>
                            <span>Allow Multiple Simultaneous Logins</span>
                        </label>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="account_lockout_threshold">Account Lockout Threshold (failed attempts)</label>
                            <input type="number" id="account_lockout_threshold" name="account_lockout_threshold" 
                                   class="form-control" min="3" max="10"
                                   value="{{ $settingsArray['account_lockout_threshold'] ?? '5' }}">
                        </div>

                        <div class="form-group">
                            <label for="account_lockout_duration">Lockout Duration (minutes)</label>
                            <input type="number" id="account_lockout_duration" name="account_lockout_duration" 
                                   class="form-control" min="5" max="60"
                                   value="{{ $settingsArray['account_lockout_duration'] ?? '15' }}">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" onclick="resetSettings('auth')">Reset to Defaults</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="settings-panel" data-group="notification">
            <div class="panel-header">
                <h2>
                    <svg class="panel-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                    </svg>
                    Notification Settings
                </h2>
                <p class="panel-description">Manage notification preferences and channels</p>
            </div>
            <div class="panel-body">
                <form id="notification-settings-form" class="settings-form">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="email_notifications_enabled" value="1"
                                   {{ isset($settingsArray['email_notifications_enabled']) && $settingsArray['email_notifications_enabled'] == '1' ? 'checked' : '' }}>
                            <span>Enable Email Notifications</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="in_app_notifications_enabled" value="1"
                                   {{ isset($settingsArray['in_app_notifications_enabled']) && $settingsArray['in_app_notifications_enabled'] == '1' ? 'checked' : '' }}>
                            <span>Enable In-App Notifications</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="sms_notifications_enabled" value="1" id="sms_enabled"
                                   {{ isset($settingsArray['sms_notifications_enabled']) && $settingsArray['sms_notifications_enabled'] == '1' ? 'checked' : '' }}>
                            <span>Enable SMS Notifications</span>
                        </label>
                    </div>

                    <div class="sms-settings" id="sms-settings" style="display: {{ isset($settingsArray['sms_notifications_enabled']) && $settingsArray['sms_notifications_enabled'] == '1' ? 'block' : 'none' }};">
                        <div class="form-group">
                            <label for="sms_provider">SMS Provider</label>
                            <input type="text" id="sms_provider" name="sms_provider" class="form-control" 
                                   value="{{ $settingsArray['sms_provider'] ?? '' }}" 
                                   placeholder="e.g., Twilio, Nexmo">
                        </div>

                        <div class="form-group">
                            <label for="sms_api_key">SMS API Key</label>
                            <input type="password" id="sms_api_key" name="sms_api_key" class="form-control" 
                                   value="{{ $settingsArray['sms_api_key'] ?? '' }}" 
                                   placeholder="Enter API key">
                            <small class="form-text">API key will be encrypted and stored securely</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" onclick="resetSettings('notification')">Reset to Defaults</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Data & Privacy Settings -->
        <div class="settings-panel" data-group="privacy">
            <div class="panel-header">
                <h2>
                    <svg class="panel-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                    </svg>
                    Data & Privacy Settings
                </h2>
                <p class="panel-description">Configure data retention and privacy policies</p>
            </div>
            <div class="panel-body">
                <form id="privacy-settings-form" class="settings-form">
                    <div class="form-group">
                        <label for="data_retention_days">Data Retention Period (days)</label>
                        <input type="number" id="data_retention_days" name="data_retention_days" 
                               class="form-control" min="30" max="3650"
                               value="{{ $settingsArray['data_retention_days'] ?? '365' }}">
                        <small class="form-text">How long to retain user data (30-3650 days)</small>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="auto_cleanup_logs" value="1" id="auto_cleanup_logs"
                                   {{ isset($settingsArray['auto_cleanup_logs']) && $settingsArray['auto_cleanup_logs'] == '1' ? 'checked' : '' }}>
                            <span>Enable Automatic Log Cleanup</span>
                        </label>
                    </div>

                    <div class="form-group" id="cleanup-days-group" style="display: {{ isset($settingsArray['auto_cleanup_logs']) && $settingsArray['auto_cleanup_logs'] == '1' ? 'block' : 'none' }};">
                        <label for="auto_cleanup_logs_days">Auto Cleanup Logs After (days)</label>
                        <input type="number" id="auto_cleanup_logs_days" name="auto_cleanup_logs_days" 
                               class="form-control" min="30" max="365"
                               value="{{ $settingsArray['auto_cleanup_logs_days'] ?? '90' }}">
                    </div>

                    <div class="form-group">
                        <label for="user_deletion_behavior">User Deletion Behavior</label>
                        <select id="user_deletion_behavior" name="user_deletion_behavior" class="form-control">
                            <option value="soft_delete" {{ (isset($settingsArray['user_deletion_behavior']) && $settingsArray['user_deletion_behavior'] == 'soft_delete') ? 'selected' : '' }}>
                                Soft Delete (Recommended)
                            </option>
                            <option value="hard_delete" {{ (isset($settingsArray['user_deletion_behavior']) && $settingsArray['user_deletion_behavior'] == 'hard_delete') ? 'selected' : '' }}>
                                Hard Delete (Permanent)
                            </option>
                            <option value="anonymize" {{ (isset($settingsArray['user_deletion_behavior']) && $settingsArray['user_deletion_behavior'] == 'anonymize') ? 'selected' : '' }}>
                                Anonymize Data
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="export_system_data_enabled" value="1"
                                   {{ isset($settingsArray['export_system_data_enabled']) && $settingsArray['export_system_data_enabled'] == '1' ? 'checked' : '' }}>
                            <span>Enable System Data Export</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" onclick="resetSettings('privacy')">Reset to Defaults</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Platform Preferences -->
        <div class="settings-panel" data-group="platform">
            <div class="panel-header">
                <h2>
                    <svg class="panel-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    Platform Preferences
                </h2>
                <p class="panel-description">Configure timezone, currency, and display formats</p>
            </div>
            <div class="panel-body">
                <form id="platform-settings-form" class="settings-form">
                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select id="timezone" name="timezone" class="form-control">
                            @php
                                $timezones = timezone_identifiers_list();
                                $currentTimezone = $settingsArray['timezone'] ?? 'UTC';
                            @endphp
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ $tz === $currentTimezone ? 'selected' : '' }}>
                                    {{ $tz }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="currency">Currency Code</label>
                            <input type="text" id="currency" name="currency" class="form-control" 
                                   value="{{ $settingsArray['currency'] ?? 'USD' }}" 
                                   placeholder="USD" maxlength="3" style="text-transform: uppercase;">
                        </div>

                        <div class="form-group">
                            <label for="currency_symbol">Currency Symbol</label>
                            <input type="text" id="currency_symbol" name="currency_symbol" class="form-control" 
                                   value="{{ $settingsArray['currency_symbol'] ?? '$' }}" 
                                   placeholder="$" maxlength="5">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="date_format">Date Format</label>
                            <select id="date_format" name="date_format" class="form-control">
                                <option value="Y-m-d" {{ (isset($settingsArray['date_format']) && $settingsArray['date_format'] == 'Y-m-d') ? 'selected' : '' }}>YYYY-MM-DD</option>
                                <option value="d/m/Y" {{ (isset($settingsArray['date_format']) && $settingsArray['date_format'] == 'd/m/Y') ? 'selected' : '' }}>DD/MM/YYYY</option>
                                <option value="m/d/Y" {{ (isset($settingsArray['date_format']) && $settingsArray['date_format'] == 'm/d/Y') ? 'selected' : '' }}>MM/DD/YYYY</option>
                                <option value="Y/m/d" {{ (isset($settingsArray['date_format']) && $settingsArray['date_format'] == 'Y/m/d') ? 'selected' : '' }}>YYYY/MM/DD</option>
                                <option value="d-m-Y" {{ (isset($settingsArray['date_format']) && $settingsArray['date_format'] == 'd-m-Y') ? 'selected' : '' }}>DD-MM-YYYY</option>
                                <option value="m-d-Y" {{ (isset($settingsArray['date_format']) && $settingsArray['date_format'] == 'm-d-Y') ? 'selected' : '' }}>MM-DD-YYYY</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="time_format">Time Format</label>
                            <select id="time_format" name="time_format" class="form-control">
                                <option value="H:i:s" {{ (isset($settingsArray['time_format']) && $settingsArray['time_format'] == 'H:i:s') ? 'selected' : '' }}>24-hour (HH:MM:SS)</option>
                                <option value="H:i" {{ (isset($settingsArray['time_format']) && $settingsArray['time_format'] == 'H:i') ? 'selected' : '' }}>24-hour (HH:MM)</option>
                                <option value="h:i:s A" {{ (isset($settingsArray['time_format']) && $settingsArray['time_format'] == 'h:i:s A') ? 'selected' : '' }}>12-hour (HH:MM:SS AM/PM)</option>
                                <option value="h:i A" {{ (isset($settingsArray['time_format']) && $settingsArray['time_format'] == 'h:i A') ? 'selected' : '' }}>12-hour (HH:MM AM/PM)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" onclick="resetSettings('platform')">Reset to Defaults</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/admin-settings.js') }}"></script>
@endpush
@endsection

