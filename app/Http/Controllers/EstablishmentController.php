<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EstablishmentController extends Controller
{
    public function dashboard()
    {
        // Check if user is logged in and is an establishment
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access the dashboard.');
        }

        $user = $this->getUserData();
        return view('establishment.dashboard', compact('user'));
    }

    /**
     * Get user data from session
     */
    private function getUserData()
    {
        return (object) [
            'id' => Session::get('user_id'),
            'name' => Session::get('user_name', 'User'),
            'fname' => Session::get('fname', ''),
            'lname' => Session::get('lname', ''),
            'email' => Session::get('user_email'),
            'user_type' => Session::get('user_type'),
        ];
    }

    public function listingManagement()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.listing-management');
    }

    public function orderManagement()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.order-management');
    }

    public function announcements()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.announcements');
    }

    public function earnings()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.earnings');
    }

    public function donationHub()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.donation-hub');
    }

    public function impactReports()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.impact-reports');
    }

    public function settings()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.settings');
    }

    public function help()
    {
        if (!Session::has('user_id') || Session::get('user_type') !== 'establishment') {
            return redirect()->route('login')->with('error', 'Please login as an establishment to access this page.');
        }

        return view('establishment.help');
    }
}
