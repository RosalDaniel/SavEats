<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consumer;
use App\Models\Establishment;
use App\Models\Foodbank;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Get user data from session and appropriate model
     */
    private function getUserData()
    {
        $userId = session('user_id');
        $userType = session('user_type');
        
        if (!$userId || !$userType) {
            return null;
        }

        switch ($userType) {
            case 'consumer':
                return Consumer::find($userId);
            case 'establishment':
                return Establishment::find($userId);
            case 'foodbank':
                return Foodbank::find($userId);
            case 'admin':
                return User::find($userId);
            default:
                return null;
        }
    }

    /**
     * Show consumer dashboard
     */
    public function consumer()
    {
        $user = $this->getUserData();
        return view('consumer.dashboard', compact('user'));
    }

    /**
     * Show establishment dashboard
     */
    public function establishment()
    {
        $user = $this->getUserData();
        return view('establishment.dashboard', compact('user'));
    }

    /**
     * Show foodbank dashboard
     */
    public function foodbank()
    {
        $user = $this->getUserData();
        return view('foodbank.dashboard', compact('user'));
    }

    /**
     * Show help center for foodbank
     */
    public function foodbankHelp()
    {
        return view('foodbank.help');
    }

    /**
     * Show settings page for foodbank
     */
    public function foodbankSettings()
    {
        $userData = $this->getUserData();
        return view('foodbank.settings', compact('userData'));
    }

    /**
     * Show admin dashboard
     */
    public function admin()
    {
        $user = $this->getUserData();
        return view('admin.dashboard', compact('user'));
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = $this->getUserData();
        $userType = session('user_type', 'consumer');
        
        return view($userType . '.profile', compact('user', 'userType'));
    }
}
