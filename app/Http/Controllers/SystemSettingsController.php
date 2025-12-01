<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SystemSettingsController extends Controller
{
    /**
     * Get user data helper
     */
    private function getUserData()
    {
        return [
            'id' => session('user_id'),
            'username' => session('user_name'),
            'email' => session('user_email'),
            'type' => session('user_type'),
        ];
    }

    /**
     * Display system settings page
     */
    public function index()
    {
        if (session('user_type') !== 'admin') {
            return redirect()->route('login')->with('error', 'Access denied.');
        }

        $user = $this->getUserData();
        
        // Initialize default settings if they don't exist
        $this->initializeDefaultSettings();
        
        // Get all settings grouped by category
        $settings = Settings::all()->groupBy('group');
        
        // Convert to key-value pairs for easy access
        $settingsArray = [];
        foreach ($settings as $group => $groupSettings) {
            foreach ($groupSettings as $setting) {
                $settingsArray[$setting->key] = $setting->value;
            }
        }

        return view('admin.settings', compact('user', 'settingsArray'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $group = $request->input('group');
        $changedSettings = [];
        
        try {
            // Handle different setting groups
            switch ($group) {
                case 'general':
                    $changedSettings = $this->updateGeneralSettings($request);
                    break;
                case 'auth':
                    $changedSettings = $this->updateAuthSettings($request);
                    break;
                case 'notification':
                    $changedSettings = $this->updateNotificationSettings($request);
                    break;
                case 'privacy':
                    $changedSettings = $this->updatePrivacySettings($request);
                    break;
                case 'platform':
                    $changedSettings = $this->updatePlatformSettings($request);
                    break;
                default:
                    return response()->json(['error' => 'Invalid settings group.'], 400);
            }

            // Log changes
            if (!empty($changedSettings)) {
                $changes = [];
                foreach ($changedSettings as $key => $value) {
                    $oldValue = Settings::where('key', $key)->first();
                    $changes[] = [
                        'key' => $key,
                        'old_value' => $oldValue ? $oldValue->value : null,
                        'new_value' => $value,
                    ];
                }

                SystemLog::log(
                    'system_change',
                    'settings_updated',
                    sprintf('System settings updated: %s (%d changes)', ucfirst($group), count($changes)),
                    'info',
                    'success',
                    [
                        'group' => $group,
                        'changes' => $changes,
                        'admin_id' => session('user_id'),
                        'admin_email' => session('user_email'),
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully.',
                'changes' => count($changedSettings),
            ]);

        } catch (\Exception $e) {
            SystemLog::log(
                'system_change',
                'settings_update_failed',
                'Failed to update system settings: ' . $e->getMessage(),
                'error',
                'failed',
                [
                    'group' => $group,
                    'error' => $e->getMessage(),
                ]
            );

            return response()->json([
                'error' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update general settings
     */
    private function updateGeneralSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'site_name' => 'nullable|string|max:255',
            'system_description' => 'nullable|string|max:1000',
            'platform_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png,jpg,jpeg|max:512',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $changedSettings = [];

        // Update site name
        if ($request->has('site_name')) {
            $this->setSetting('site_name', $request->input('site_name'), 'general', 'Site Name');
            $changedSettings['site_name'] = $request->input('site_name');
        }

        // Update system description
        if ($request->has('system_description')) {
            $this->setSetting('system_description', $request->input('system_description'), 'general', 'System Description');
            $changedSettings['system_description'] = $request->input('system_description');
        }

        // Handle logo upload
        if ($request->hasFile('platform_logo')) {
            $file = $request->file('platform_logo');
            $filename = 'logo.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/settings', $filename);
            $url = Storage::url('settings/' . $filename);
            
            // Delete old logo if exists
            $oldLogo = Settings::where('key', 'platform_logo')->first();
            if ($oldLogo && $oldLogo->value) {
                $oldPath = str_replace('/storage/', 'public/', $oldLogo->value);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }
            
            $this->setSetting('platform_logo', $url, 'general', 'Platform Logo');
            $changedSettings['platform_logo'] = $url;
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $filename = 'favicon.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/settings', $filename);
            $url = Storage::url('settings/' . $filename);
            
            // Delete old favicon if exists
            $oldFavicon = Settings::where('key', 'favicon')->first();
            if ($oldFavicon && $oldFavicon->value) {
                $oldPath = str_replace('/storage/', 'public/', $oldFavicon->value);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }
            
            $this->setSetting('favicon', $url, 'general', 'Favicon');
            $changedSettings['favicon'] = $url;
        }

        return $changedSettings;
    }

    /**
     * Update authentication settings
     */
    private function updateAuthSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password_min_length' => 'nullable|integer|min:6|max:32',
            'password_require_uppercase' => 'nullable|boolean',
            'password_require_lowercase' => 'nullable|boolean',
            'password_require_numbers' => 'nullable|boolean',
            'password_require_symbols' => 'nullable|boolean',
            'session_timeout' => 'nullable|integer|min:5|max:1440',
            'multi_login_allowed' => 'nullable|boolean',
            'account_lockout_threshold' => 'nullable|integer|min:3|max:10',
            'account_lockout_duration' => 'nullable|integer|min:5|max:60',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $changedSettings = [];
        $fields = [
            'password_min_length',
            'password_require_uppercase',
            'password_require_lowercase',
            'password_require_numbers',
            'password_require_symbols',
            'session_timeout',
            'multi_login_allowed',
            'account_lockout_threshold',
            'account_lockout_duration',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                if (is_bool($value) || $value === '1' || $value === '0') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                }
                $this->setSetting($field, $value, 'auth', ucwords(str_replace('_', ' ', $field)));
                $changedSettings[$field] = $value;
            }
        }

        return $changedSettings;
    }

    /**
     * Update notification settings
     */
    private function updateNotificationSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_notifications_enabled' => 'nullable|boolean',
            'in_app_notifications_enabled' => 'nullable|boolean',
            'sms_notifications_enabled' => 'nullable|boolean',
            'sms_provider' => 'nullable|string|max:255',
            'sms_api_key' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $changedSettings = [];
        $fields = [
            'email_notifications_enabled',
            'in_app_notifications_enabled',
            'sms_notifications_enabled',
            'sms_provider',
            'sms_api_key',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                if (is_bool($value) || $value === '1' || $value === '0') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                }
                $this->setSetting($field, $value, 'notification', ucwords(str_replace('_', ' ', $field)));
                $changedSettings[$field] = $field === 'sms_api_key' ? '***' : $value; // Don't log API keys
            }
        }

        return $changedSettings;
    }

    /**
     * Update privacy settings
     */
    private function updatePrivacySettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data_retention_days' => 'nullable|integer|min:30|max:3650',
            'auto_cleanup_logs' => 'nullable|boolean',
            'auto_cleanup_logs_days' => 'nullable|integer|min:30|max:365',
            'user_deletion_behavior' => 'nullable|string|in:soft_delete,hard_delete,anonymize',
            'export_system_data_enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $changedSettings = [];
        $fields = [
            'data_retention_days',
            'auto_cleanup_logs',
            'auto_cleanup_logs_days',
            'user_deletion_behavior',
            'export_system_data_enabled',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                if (is_bool($value) || $value === '1' || $value === '0') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                }
                $this->setSetting($field, $value, 'privacy', ucwords(str_replace('_', ' ', $field)));
                $changedSettings[$field] = $value;
            }
        }

        return $changedSettings;
    }

    /**
     * Update platform settings
     */
    private function updatePlatformSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timezone' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:10',
            'date_format' => 'nullable|string|in:Y-m-d,d/m/Y,m/d/Y,Y/m/d,d-m-Y,m-d-Y',
            'time_format' => 'nullable|string|in:H:i:s,H:i,h:i:s A,h:i A',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        $changedSettings = [];
        $fields = [
            'timezone',
            'currency',
            'currency_symbol',
            'date_format',
            'time_format',
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                $this->setSetting($field, $value, 'platform', ucwords(str_replace('_', ' ', $field)));
                $changedSettings[$field] = $value;
            }
        }

        return $changedSettings;
    }

    /**
     * Reset settings to defaults
     */
    public function reset(Request $request)
    {
        if (session('user_type') !== 'admin') {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $group = $request->input('group');
        
        try {
            $defaults = $this->getDefaultSettings($group);
            $resetCount = 0;

            foreach ($defaults as $key => $value) {
                $this->setSetting($key, $value, $group);
                $resetCount++;
            }

            SystemLog::log(
                'system_change',
                'settings_reset',
                sprintf('System settings reset to defaults: %s', ucfirst($group)),
                'warning',
                'success',
                [
                    'group' => $group,
                    'admin_id' => session('user_id'),
                    'admin_email' => session('user_email'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to defaults successfully.',
                'reset_count' => $resetCount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to reset settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set a setting value
     */
    private function setSetting($key, $value, $group, $description = null)
    {
        Settings::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'description' => $description,
            ]
        );
    }

    /**
     * Initialize default settings if they don't exist
     */
    private function initializeDefaultSettings()
    {
        $allDefaults = [
            'general' => [
                'site_name' => 'SavEats',
                'system_description' => 'Food rescue and donation platform',
                'platform_logo' => null,
                'favicon' => null,
            ],
            'auth' => [
                'password_min_length' => '8',
                'password_require_uppercase' => '1',
                'password_require_lowercase' => '1',
                'password_require_numbers' => '1',
                'password_require_symbols' => '0',
                'session_timeout' => '120',
                'multi_login_allowed' => '1',
                'account_lockout_threshold' => '5',
                'account_lockout_duration' => '15',
            ],
            'notification' => [
                'email_notifications_enabled' => '1',
                'in_app_notifications_enabled' => '1',
                'sms_notifications_enabled' => '0',
                'sms_provider' => '',
                'sms_api_key' => '',
            ],
            'privacy' => [
                'data_retention_days' => '365',
                'auto_cleanup_logs' => '1',
                'auto_cleanup_logs_days' => '90',
                'user_deletion_behavior' => 'soft_delete',
                'export_system_data_enabled' => '1',
            ],
            'platform' => [
                'timezone' => 'UTC',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i:s',
            ],
        ];

        foreach ($allDefaults as $group => $defaults) {
            foreach ($defaults as $key => $value) {
                if (!Settings::where('key', $key)->exists()) {
                    Settings::create([
                        'key' => $key,
                        'value' => $value,
                        'group' => $group,
                        'description' => ucwords(str_replace('_', ' ', $key)),
                    ]);
                }
            }
        }
    }

    /**
     * Get default settings for a group
     */
    private function getDefaultSettings($group)
    {
        $defaults = [
            'general' => [
                'site_name' => 'SavEats',
                'system_description' => 'Food rescue and donation platform',
                'platform_logo' => null,
                'favicon' => null,
            ],
            'auth' => [
                'password_min_length' => '8',
                'password_require_uppercase' => '1',
                'password_require_lowercase' => '1',
                'password_require_numbers' => '1',
                'password_require_symbols' => '0',
                'session_timeout' => '120',
                'multi_login_allowed' => '1',
                'account_lockout_threshold' => '5',
                'account_lockout_duration' => '15',
            ],
            'notification' => [
                'email_notifications_enabled' => '1',
                'in_app_notifications_enabled' => '1',
                'sms_notifications_enabled' => '0',
                'sms_provider' => '',
                'sms_api_key' => '',
            ],
            'privacy' => [
                'data_retention_days' => '365',
                'auto_cleanup_logs' => '1',
                'auto_cleanup_logs_days' => '90',
                'user_deletion_behavior' => 'soft_delete',
                'export_system_data_enabled' => '1',
            ],
            'platform' => [
                'timezone' => 'UTC',
                'currency' => 'USD',
                'currency_symbol' => '$',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i:s',
            ],
        ];

        return $defaults[$group] ?? [];
    }
}

