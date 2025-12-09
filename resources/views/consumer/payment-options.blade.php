@php
    use Illuminate\Support\Facades\Storage;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                <div class="payment-method active" id="cashMethod" data-method="cash">
                    <div class="method-header" onclick="toggleMethod('cashMethod')">
                        <span class="method-title">Cash On Hand</span>
                        <span class="method-arrow">▲</span>
                    </div>
                    <div class="method-content">
                        <div class="checkbox-container">
                            <input type="checkbox" id="cashCheckbox" class="payment-checkbox" checked>
                            <label for="cashCheckbox" class="checkbox-label">I will pay in cash</label>
                        </div>
                        <p class="method-description">I acknowledge that I will give it to the delivery courier to complete my payment.</p>
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

<script>
    // Pass customer data to JavaScript
    var fname = '{{ trim($userData->fname ?? "") }}';
    var lname = '{{ trim($userData->lname ?? "") }}';
    var fullName = (fname + ' ' + lname).trim();
    window.customerName = fullName || fname || 'Customer';
    window.customerPhone = '{{ $phoneNumber ?? "" }}';
    
    // Debug: Log customer data
    console.log('Customer Name:', window.customerName);
    console.log('Customer Phone:', window.customerPhone);
</script>
<script src="{{ asset('js/payment-options.js') }}"></script>
</body>
</html>
