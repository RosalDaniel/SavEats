<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - SavEats</title>
    <link href="https://fonts.googleapis.com/css2?family=Afacad&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/order-confirmation.css') }}">
</head>
<body>
<div class="order-confirmation-page">
    <!-- Header with Back Button -->
    <div class="order-header">
        <button class="back-button" onclick="goBack()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
            </svg>
        </button>
        <h1 class="order-title">Order Confirmation</h1>
    </div>

<div class="order-confirmation-container">
    
    <div class="order-content">
        <!-- Left Section: Product and Price Breakdown -->
        <div class="left-section">
             <div class="product-section">
                 <div class="product-header">
                     <h2 class="product-name" id="productName">{{ $foodItem->name }}</h2>
                     <p class="bakery-name" id="bakeryName">{{ $establishmentName }}</p>
                 </div>
                 
                 <div class="product-image">
                     @if($foodItem->image_path)
                         <img id="productImage" src="{{ Storage::url($foodItem->image_path) }}" alt="{{ $foodItem->name }}" />
                     @else
                         <div class="image-placeholder">
                             <div class="placeholder-content">
                                 <div class="placeholder-icon">🍽️</div>
                                 <div class="placeholder-text">{{ $foodItem->name }}</div>
                             </div>
                         </div>
                     @endif
                 </div>
                 
                 <div class="pricing-info">
                     <div class="current-price">₱ <span id="currentPrice">{{ number_format($discountedPrice, 2) }}</span></div>
                     @if($discountPercentage > 0)
                         <div class="discount-badge" id="discountBadge">{{ round($discountPercentage) }}% off</div>
                     @endif
                     <div class="original-price">₱ <span id="originalPrice">{{ number_format($originalPrice, 2) }}</span></div>
                 </div>
                 
                 <div class="product-details">
                     <div class="detail-item">
                         <svg class="detail-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                             <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                         </svg>
                         <span id="location">{{ $establishmentAddress }}</span>
                     </div>
                     <div class="detail-item">
                         <svg class="detail-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                             <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                         </svg>
                         <span id="pickupOption">{{ $foodItem->pickup_available ? 'Pick-Up Available' : 'Delivery Only' }}</span>
                     </div>
                     <div class="detail-row">
                         <div class="detail-item">
                             <svg class="detail-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                 <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                             </svg>
                             <span id="expiryDate">Expiry Date: {{ $foodItem->expiry_date->format('F j, Y') }}</span>
                         </div>
                         <div class="operating-hours" id="operatingHours">Mon - Sat | 7:00 am - 5:00 pm</div>
                     </div>
                 </div>
             </div>
            
             <!-- Price Breakdown -->
             <div class="price-breakdown">
                 <h3 class="breakdown-title">Price Breakdown</h3>
                 <div class="breakdown-table">
                     <div class="table-header">
                         <span>Item</span>
                         <span>Qty</span>
                         <span>Price</span>
                     </div>
                     <div class="table-row">
                         <span id="itemName">{{ $foodItem->name }}</span>
                         <span id="itemQuantity">{{ $quantity }}</span>
                         <span id="itemPrice">₱ {{ number_format($discountedPrice, 2) }}</span>
                     </div>
                     <div class="table-total">
                         <span>TOTAL</span>
                         <span id="totalPrice">₱ {{ number_format($discountedPrice * $quantity, 2) }}</span>
                     </div>
                 </div>
             </div>
        </div>
        
        <!-- Right Section: Order Details -->
        <div class="right-section">
            <!-- Order Details Container -->
            <div class="order-details-container">
                <!-- Receive Method -->
                <div class="receive-method-section">
                    <h3 class="section-title">Receive Method</h3>
                    <div class="method-buttons">
                        <button class="method-btn active" id="pickupBtn" data-method="pickup">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            Pick-Up
                        </button>
                        <button class="method-btn" id="deliveryBtn" data-method="delivery">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                            </svg>
                            Delivery
                        </button>
                    </div>
                    <div class="method-note">
                        <span class="note-text">Note: Please choose your preferred method of receiving your order.</span>
                    </div>
                </div>
                
                <!-- Map Section -->
                <div class="map-section">
                    <div class="map-placeholder">
                        <div class="map-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                        </div>
                        <div class="map-text">
                            <h3>Interactive Map</h3>
                            <p>Map will be displayed here</p>
                        </div>
                    </div>
                </div>
                
                <!-- Bakery Contact Details -->
                <div class="contact-details">
                    <div class="contact-item">
                        <svg class="contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <span id="bakeryContactName">{{ $establishmentName }}</span>
                    </div>
                    <div class="contact-item">
                        <svg class="contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                        <span id="bakeryAddress">{{ $establishmentAddress }}</span>
                    </div>
                    <div class="contact-item">
                        <svg class="contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                        </svg>
                        <span id="bakeryPhone">{{ $foodItem->establishment->contact_number ?? 'Contact not available' }}</span>
                    </div>
                </div>
                
                <!-- Customer Details Form -->
                <div class="customer-details">
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <div class="phone-input-container">
                            <span class="country-code">+63</span>
                            <input type="tel" id="phoneNumber" placeholder="Enter your Phone No.">
                            <svg class="phone-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Arrival Time</label>
                        <div class="time-inputs">
                            <div class="time-input-group">
                                <label for="startTime">Start Time</label>
                                <select id="startTime">
                                    <option value="">Select Start Time</option>
                                    <option value="07:00">7:00 AM</option>
                                    <option value="08:00">8:00 AM</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                    <option value="17:00">5:00 PM</option>
                                </select>
                            </div>
                            <div class="time-input-group">
                                <label for="endTime">End Time</label>
                                <select id="endTime">
                                    <option value="">Select End Time</option>
                                    <option value="07:00">7:00 AM</option>
                                    <option value="08:00">8:00 AM</option>
                                    <option value="09:00">9:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="12:00">12:00 PM</option>
                                    <option value="13:00">1:00 PM</option>
                                    <option value="14:00">2:00 PM</option>
                                    <option value="15:00">3:00 PM</option>
                                    <option value="16:00">4:00 PM</option>
                                    <option value="17:00">5:00 PM</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-note">
                        <span class="note-text">Note: Kindly indicate any special arrangements or identifying details for pickup.</span>
                    </div>
                </div>
                
                <!-- Proceed to Payment Button -->
                <div class="payment-section">
                    <button class="proceed-payment-btn" id="proceedPaymentBtn">
                        Proceed to Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script src="{{ asset('js/order-confirmation.js') }}"></script>
</body>
</html>
