@extends('layouts.consumer')

@section('title', 'Help Center | SavEats')

@section('header', 'Help Center')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/help-center.css') }}">
@endsection

@section('content')
<div class="help-center-container">
    <!-- Search Section -->
    <div class="search-section">
        <div class="search-container">
            <h2 class="search-title">How can we help you?</h2>
            <div class="search-box">
                <input type="text" id="helpSearch" placeholder="Search for help topics, questions, or issues..." class="search-input">
                <button class="search-btn" onclick="searchHelp()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Help Categories -->
    <div class="quick-help-section">
        <h3 class="section-title">Quick Help</h3>
        <div class="help-categories">
            <div class="help-category" onclick="scrollToFAQ('faq-place-order')">
                <div class="category-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <h4>Getting Started</h4>
                <p>Learn how to use SavEats as a consumer</p>
            </div>
            
            <div class="help-category" onclick="scrollToFAQ('faq-browse-items')">
                <div class="category-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <h4>Browsing & Shopping</h4>
                <p>Find and purchase food items</p>
            </div>
            
            <div class="help-category" onclick="scrollToFAQ('faq-payment-methods')">
                <div class="category-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                </div>
                <h4>Orders & Payments</h4>
                <p>Manage your orders and payments</p>
            </div>
            
            <div class="help-category" onclick="scrollToFAQ('faq-delivery-pickup')">
                <div class="category-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 7h-8v6h8V7zm-2 4h-4V9h4v2zm4-12H3C1.9 1 1 1.9 1 3v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V3c0-1.1-.9-2-2-2zm0 16H3V5h14v14z"/>
                    </svg>
                </div>
                <h4>Delivery & Pickup</h4>
                <p>Delivery options and pickup locations</p>
            </div>
            
            <div class="help-category" onclick="scrollToFAQ('faq-update-profile')">
                <div class="category-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
                <h4>Account & Profile</h4>
                <p>Manage your account settings</p>
            </div>
            
            <div class="help-category" onclick="scrollToFAQ('faq-order-problem')">
                <div class="category-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <h4>Troubleshooting</h4>
                <p>Common issues and solutions</p>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="faq-section">
        <h3 class="section-title">Frequently Asked Questions</h3>
        <div class="faq-list">
            <div class="faq-item" id="faq-browse-items">
                <div class="faq-question">
                    <h4>How do I browse and find food items?</h4>
                    <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5z"/>
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>To browse food items on SavEats:</p>
                    <ol>
                        <li>Go to the "Food Listing" page from your dashboard</li>
                        <li>Use the search bar to find specific items</li>
                        <li>Filter by category, price range, or location</li>
                        <li>Click on any item to view detailed information</li>
                        <li>Check availability and pickup/delivery options</li>
                    </ol>
                </div>
            </div>
            
            <div class="faq-item" id="faq-place-order">
                <div class="faq-question">
                    <h4>How do I place an order?</h4>
                    <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5z"/>
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>Placing an order is simple:</p>
                    <ol>
                        <li>Browse and select the food item you want</li>
                        <li>Choose quantity and pickup/delivery method</li>
                        <li>Review your order details</li>
                        <li>Proceed to payment options</li>
                        <li>Complete payment and confirm your order</li>
                        <li>Wait for confirmation from the establishment</li>
                    </ol>
                </div>
            </div>
            
            <div class="faq-item" id="faq-payment-methods">
                <div class="faq-question">
                    <h4>What payment methods are accepted?</h4>
                    <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5z"/>
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>SavEats accepts various payment methods:</p>
                    <ul>
                        <li>Credit/Debit Cards (Visa, Mastercard)</li>
                        <li>E-Wallets (GCash, PayMaya, GrabPay)</li>
                        <li>Cash on Delivery/Pickup</li>
                        <li>Bank Transfers</li>
                        <li>Digital Banking Apps</li>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item" id="faq-track-orders">
                <div class="faq-question">
                    <h4>How do I track my orders?</h4>
                    <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5z"/>
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>Track your orders easily:</p>
                    <ul>
                        <li>Go to "My Orders" from your dashboard</li>
                        <li>View all your current and past orders</li>
                        <li>Check order status (pending, confirmed, preparing, ready, completed)</li>
                        <li>View order details and estimated times</li>
                        <li>Contact the establishment if needed</li>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item" id="faq-delivery-pickup">
                <div class="faq-question">
                    <h4>What are the delivery and pickup options?</h4>
                    <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5z"/>
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>SavEats offers flexible options:</p>
                    <ul>
                        <li><strong>Pickup:</strong> Collect your order from the establishment</li>
                        <li><strong>Delivery:</strong> Have your order delivered to your location</li>
                        <li>Check availability for each item before ordering</li>
                        <li>Delivery fees may apply depending on distance</li>
                        <li>Pickup times are usually faster than delivery</li>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item" id="faq-update-profile">
                <div class="faq-question">
                    <h4>How do I update my profile information?</h4>
                    <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5z"/>
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>To update your profile:</p>
                    <ol>
                        <li>Click on your profile picture/name in the sidebar</li>
                        <li>Go to the "Account Profile" page</li>
                        <li>Click "Edit" on the section you want to update</li>
                        <li>Make your changes and save</li>
                        <li>Your information will be updated immediately</li>
                    </ol>
                </div>
            </div>
            
            <div class="faq-item" id="faq-order-problem">
                <div class="faq-question">
                    <h4>What if I have a problem with my order?</h4>
                    <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5z"/>
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>If you encounter any issues:</p>
                    <ul>
                        <li>Check your order status in "My Orders"</li>
                        <li>Contact the establishment directly through the platform</li>
                        <li>Use our customer support channels</li>
                        <li>Report the issue with order details</li>
                        <li>We'll help resolve the problem quickly</li>
                    </ul>
                </div>
            </div>
            
            <div class="faq-item" id="faq-contact-support">
                <div class="faq-question">
                    <h4>How do I contact customer support?</h4>
                    <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 10l5 5 5-5z"/>
                    </svg>
                </div>
                <div class="faq-answer">
                    <p>You can reach our support team through:</p>
                    <ul>
                        <li>Email: support@saveats.com</li>
                        <li>Phone: +63 2 1234 5678</li>
                        <li>Live Chat: Available 24/7 on the platform</li>
                        <li>Help Center: Search our knowledge base</li>
                        <li>Social Media: @SaveatsOfficial</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>        
    <!-- Links to Terms & Privacy -->
    <div class="legal-links-section">
        <h3 class="section-title">Legal & Policies</h3>
        <div class="legal-links">
            <a href="{{ route('terms') }}" class="legal-link">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                </svg>
                <span>Terms & Conditions</span>
            </a>
            <a href="{{ route('privacy') }}" class="legal-link">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                </svg>
                <span>Privacy Policy</span>
            </a>
        </div>
    </div>

    <!-- Contact Support Section -->
    <div class="contact-section">
        <h3 class="section-title">Still Need Help?</h3>
        <div class="contact-options">
            <div class="contact-option">
                <div class="contact-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                    </svg>
                </div>
                <h4>Email Support</h4>
                <p class="contact-text">
                    For assistance, email us at 
                    <span class="copyable-text" onclick="copyToClipboard('dnpn124@gmail.com', this)" title="Click to copy">dnpn124@gmail.com</span>
                </p>
            </div>
            
            <div class="contact-option">
                <div class="contact-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                    </svg>
                </div>
                <h4>Phone Support</h4>
                <p class="contact-text">
                    For urgent concerns, contact SavEats at 
                    <span class="copyable-text" onclick="copyToClipboard('09123456789', this)" title="Click to copy">09273940559</span>
                </p>
            </div>
            
            <div class="contact-option">
                <div class="contact-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
                    </svg>
                </div>
                <h4>Live Chat</h4>
                <p>Chat with our support team instantly</p>
                <button class="contact-btn" onclick="startLiveChat()">Start Chat</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/help-center.js') }}"></script>
@endsection
