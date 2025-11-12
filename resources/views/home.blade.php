@extends('layouts.app')

@section('title', 'SaveEats')

@section('styles')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
@endsection

@section('content')
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Fighting Food Waste, One Meal at a Time.</h1>
                <p class="hero-subtitle">SaveEats connects surplus food from businesses to people and food banks because no good food should go to waste.</p>
                <a href="#" class="cta-button">
                    Get Started →
                </a>
            </div>
            <div class="hero-illustrations">
                <img src="{{ asset('images/heroSection_img.png') }}" alt="Food illustrations" class="hero-image">
            </div>
        </div>
        <div class="hero-wave">
            <svg class="wave-svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M0,60 Q300,20 600,60 T1200,60 L1200,120 L0,120 Z" fill="white"/>
            </svg>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="section-container">
            <h2 class="section-title">Start Saving, Sharing, and Supporting</h2>
            <div class="steps-grid">
                <div class="step-card">
                    <img src="{{ asset('images/listSurplus_img.png') }}" alt="list" class="step-icon">
                    <h3 class="step-title">List Surplus Food</h3>
                    <p class="step-description">Businesses post food they can no longer sell but is still safe to eat.</p>
                </div>
                <div class="step-card">
                    <img src="{{ asset('images/browseChoose_img.png') }}" alt="browse" class="step-icon">
                    <h3 class="step-title">Browse & Choose</h3>
                    <p class="step-description">Users and food banks find nearby discounted meals or donations.</p>
                </div>
                <div class="step-card">
                    <img src="{{ asset('images/pickUp_img.png') }}" alt="pick-up" class="step-icon">
                    <h3 class="step-title">Pick Up & Enjoy</h3>
                    <p class="step-description">Meals are picked up before they go to waste. Everyone wins.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="section-container">
            <h2 class="section-title">Be Part of the Food-Saving Movement</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">"We reduced daily waste by 40%. We redirected our daily food waste by almost half, and gained new loyal customers."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">C</div>
                        <div class="author-info">
                            <h4>Celang's Bakeshop</h4>
                            <p>Bakery Section, Colon March 2024</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"I now afford meals daily for my family. I get great meals at low prices and I love supporting local shops."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">M</div>
                        <div class="author-info">
                            <h4>Marianne Joy Napisa</h4>
                            <p>Family Section, Lapu May 2024</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">"SaveEats makes it easier to coordinate pickups and reach more families in need."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">H</div>
                        <div class="author-info">
                            <h4>Henry's Donation Centre</h4>
                            <p>Food Bank Section, Cebu April 2024</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="cta-content">
            <div class="cta-text">
                <h2 class="cta-title">JOIN THE MOVEMENT TO MAKE FOOD GO FURTHER</h2>
                <p class="cta-description">Connect with a community that's saving good food, supporting local businesses, and feeding more people—one meal at a time.</p>
            </div>
            <div class="cta-button-wrapper">
                <a href="#" class="btn-cta">Join Us Now <span class="arrow-icon">→</span></a>
            </div>
        </div>
    </section>
    
    <div class="cta-divider"></div>
@endsection

@section('scripts')
    <script src="{{ asset('js/home.js') }}"></script>
@endsection