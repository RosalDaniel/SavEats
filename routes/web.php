<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/registration', function () {
    return view('auth.registration');
})->name('registration');

Route::get('/register', function () {
    return view('auth.registration');
})->name('register');

// Authentication routes (POST methods for form submissions)
Route::post('/login', function () {
    // This will be handled by your authentication logic later
    return redirect()->route('home')->with('success', 'Login functionality coming soon!');
})->name('login.submit');

Route::post('/register', function () {
    // This will be handled by your registration logic later
    return redirect()->route('home')->with('success', 'Registration functionality coming soon!');
})->name('register.submit');

// Dashboard routes (protected routes - will need authentication middleware later)
Route::get('/dashboard', function () {
    return view('consumer.dashboard');
})->name('dashboard');

Route::get('/profile', function () {
    return view('consumer.profile');
})->name('profile');
