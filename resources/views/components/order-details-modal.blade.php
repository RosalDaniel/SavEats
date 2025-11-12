<!-- Order Details Modal -->
<div class="order-details-modal" id="orderDetailsModal" role="dialog" aria-labelledby="orderDetailsModalTitle" aria-modal="true">
    <div class="order-details-modal-overlay" aria-hidden="true"></div>
    <div class="order-details-modal-container">
        <!-- Modal Header -->
        <div class="order-details-modal-header">
            <button class="order-details-modal-close" onclick="closeOrderDetailsModal()" aria-label="Close modal">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
            <h2 class="order-details-modal-title" id="orderDetailsModalTitle">Order Receipt</h2>
        </div>

        <!-- Modal Content -->
        <div class="order-details-modal-content">
            <!-- Status Header Card -->
            <div class="order-status-header">
                <div class="status-header-content">
                    <h3 class="order-status-text" id="orderStatusText">Pending Order</h3>
                    <div class="status-illustration">
                        <svg width="100" height="100" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Delivery person with box illustration -->
                            <!-- Head -->
                            <circle cx="60" cy="35" r="14" fill="#4a7c59"/>
                            <!-- Body -->
                            <rect x="48" y="49" width="24" height="30" rx="3" fill="#4a7c59"/>
                            <!-- Legs -->
                            <rect x="50" y="79" width="12" height="18" rx="2" fill="#4a7c59"/>
                            <rect x="58" y="79" width="12" height="18" rx="2" fill="#4a7c59"/>
                            <!-- Shirt detail -->
                            <rect x="52" y="55" width="16" height="10" rx="1" fill="#fff"/>
                            <!-- Cap -->
                            <rect x="50" y="22" width="20" height="14" rx="2" fill="#4a7c59"/>
                            <rect x="48" y="20" width="24" height="4" rx="2" fill="#4a7c59"/>
                            <!-- Box being carried -->
                            <rect x="70" y="50" width="18" height="20" rx="2" fill="#8B4513" stroke="#654321" stroke-width="1"/>
                            <line x1="70" y1="55" x2="88" y2="55" stroke="#654321" stroke-width="1"/>
                            <line x1="70" y1="60" x2="88" y2="60" stroke="#654321" stroke-width="1"/>
                            <circle cx="75" cy="63" r="2" fill="#fff"/>
                            <circle cx="83" cy="63" r="2" fill="#fff"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Order Information -->
            <div class="order-info-section">
                <div class="info-row">
                    <span class="info-label">Order Number</span>
                    <span class="info-value" id="orderNumber">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date and Time</span>
                    <span class="info-value" id="orderDateTime">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Payment Method</span>
                    <span class="info-value" id="paymentMethod">-</span>
                </div>
                <div class="info-divider"></div>
                <div class="info-row">
                    <span class="info-label">Customer Name</span>
                    <span class="info-value" id="customerName">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Contact No.</span>
                    <span class="info-value" id="customerPhone">-</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email Address</span>
                    <span class="info-value" id="customerEmail">-</span>
                </div>
            </div>

            <!-- Order Details -->
            <div class="order-items-section">
                <h4 class="section-title">Order Details</h4>
                <div class="order-items-table">
                    <div class="order-item-header">
                        <span class="item-col-name">Items</span>
                        <span class="item-col-qty">Quantity</span>
                        <span class="item-col-price">Price</span>
                    </div>
                    <div class="order-items-list" id="orderItemsList">
                        <!-- Items will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="order-summary-section">
                <div class="summary-row">
                    <span class="summary-label">Delivery Fee</span>
                    <span class="summary-value" id="deliveryFee">₱ 0.00</span>
                </div>
                <div class="summary-row total-row">
                    <span class="summary-label">TOTAL</span>
                    <span class="summary-value total-amount" id="totalAmount">₱ 0.00</span>
                </div>
            </div>

            <!-- Pickup Information -->
            <div class="pickup-info-section">
                <div class="pickup-info-item">
                    <div class="pickup-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                        </svg>
                    </div>
                    <div class="pickup-info-content">
                        <span class="pickup-label">Delivery Method</span>
                        <span class="pickup-value" id="deliveryMethod">-</span>
                    </div>
                </div>
                <div class="pickup-info-item">
                    <div class="pickup-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </div>
                    <div class="pickup-info-content">
                        <span class="pickup-label">Store</span>
                        <span class="pickup-value" id="storeName">-</span>
                    </div>
                </div>
                <div class="pickup-info-item">
                    <div class="pickup-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </div>
                    <div class="pickup-info-content">
                        <span class="pickup-label">Location</span>
                        <span class="pickup-value" id="storeAddress">-</span>
                    </div>
                </div>
            </div>

            <!-- Note Section -->
            <div class="order-note-section">
                <p class="order-note">
                    Note: Kindly have the donation items packed and ready at the designated pickup location. Ensure perishables are safely handled and labeled if necessary.
                </p>
            </div>
        </div>
    </div>
</div>

