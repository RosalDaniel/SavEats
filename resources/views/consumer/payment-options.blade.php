@php
    use Illuminate\Support\Facades\Storage;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Options | SavEats</title>
    <link href="https://fonts.googleapis.com/css2?family=Afacad&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/payment-options.css') }}">
</head>
<body>
<div class="payment-options-page">
    <!-- Header with Back Button -->
    <div class="payment-header">
        <button class="back-button" onclick="goBack()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
            </svg>
        </button>
        <h1 class="payment-title">Payment Options</h1>
    </div>

    <div class="payment-container">
        <div class="payment-content">
            <!-- Payment Breakdown Section -->
            <div class="payment-breakdown-section">
                <h2 class="breakdown-title">Payment Breakdown</h2>
                <div class="breakdown-table">
                    <div class="table-header">
                        <div class="fees-column">Fees</div>
                        <div class="price-column">Price</div>
                    </div>
                    <div class="table-row">
                        <div class="fees-column">{{ $foodItem->name }}</div>
                        <div class="price-column">₱ {{ number_format($unitPrice, 2) }}</div>
                    </div>
                    @if($deliveryFee > 0)
                    <div class="table-row">
                        <div class="fees-column">Delivery Fee</div>
                        <div class="price-column">₱ {{ number_format($deliveryFee, 2) }}</div>
                    </div>
                    @endif
                    <div class="table-total">
                        <div class="fees-column">TOTAL</div>
                        <div class="price-column">₱ {{ number_format($total, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="payment-methods">
                <!-- Cash On Hand -->
                <div class="payment-method" id="cashMethod">
                    <div class="method-header" onclick="toggleMethod('cashMethod')">
                        <span class="method-title">Cash On Hand</span>
                        <span class="method-arrow">▲</span>
                    </div>
                    <div class="method-content">
                        <div class="checkbox-container">
                            <input type="checkbox" id="cashCheckbox" class="payment-checkbox">
                            <label for="cashCheckbox" class="checkbox-label">I will pay in cash</label>
                        </div>
                        <p class="method-description">I acknowledge that I will give it to the delivery courier to complete my payment.</p>
                    </div>
                </div>

                <!-- Credit/Debit Card -->
                <div class="payment-method" id="cardMethod">
                    <div class="method-header" onclick="toggleMethod('cardMethod')">
                        <span class="method-title">Credit/Debit Card</span>
                        <span class="method-arrow">▲</span>
                    </div>
                    <div class="method-content">
                        <div class="bank-logos">
                            <div class="bank-logo unionbank">
                                <div class="logo-circle">UB</div>
                                <span class="bank-name">UnionBank</span>
                            </div>
                            <div class="bank-logo bpi">
                                <div class="logo-circle">BPI</div>
                                <span class="bank-name">BPI</span>
                            </div>
                            <div class="bank-logo bdo">
                                <div class="logo-circle">BDO</div>
                                <span class="bank-name">BDO</span>
                            </div>
                            <div class="bank-logo security">
                                <div class="logo-circle">SB</div>
                                <span class="bank-name">SECURITY BANK</span>
                            </div>
                        </div>
                        
                        <div class="card-form">
                            <div class="form-group">
                                <label for="cardNumber">Card Number</label>
                                <div class="input-with-icon">
                                    <input type="text" id="cardNumber" placeholder="Enter card number">
                                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiryDate">Expiry Date</label>
                                    <div class="input-with-icon">
                                        <input type="text" id="expiryDate" placeholder="MM/YY">
                                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="securityCode">Security Code</label>
                                    <input type="text" id="securityCode" placeholder="CVV">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="foodImage">Upload Food Image</label>
                                <div class="file-upload">
                                    <input type="file" id="foodImage" accept="image/*">
                                    <div class="upload-placeholder">
                                        <svg class="upload-icon" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                        </svg>
                                        <span>Choose file</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- E-Wallet -->
                <div class="payment-method" id="ewalletMethod">
                    <div class="method-header" onclick="toggleMethod('ewalletMethod')">
                        <span class="method-title">E-Wallet</span>
                        <span class="method-arrow">▼</span>
                    </div>
                    <div class="method-content collapsed">
                        <p class="method-description">E-wallet payment options will be available here.</p>
                    </div>
                </div>
            </div>

            <!-- Place Order Button -->
            <div class="place-order-section">
                <button class="place-order-btn" id="placeOrderBtn">
                    Place Order
                </button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/payment-options.js') }}"></script>
</body>
</html>
