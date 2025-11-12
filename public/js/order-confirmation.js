// Order Confirmation Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Get URL parameters to populate order data
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    const quantity = urlParams.get('quantity') || 1;
    
    // Initialize the page
    initializeOrderConfirmation();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize map
    initializeMap();
    
    // Load product data if ID is provided
    if (productId) {
        loadProductData(productId, quantity);
    }
});

function initializeOrderConfirmation() {
    // Get the actual product price from the page
    const currentPriceElement = document.getElementById('currentPrice');
    const quantityElement = document.getElementById('itemQuantity');
    
    if (currentPriceElement && quantityElement) {
        const unitPrice = parseFloat(currentPriceElement.textContent);
        const quantity = parseInt(quantityElement.textContent) || 1;
        updatePriceBreakdown(quantity, unitPrice);
    }
}

function setupEventListeners() {
    // Receive method buttons
    const pickupBtn = document.getElementById('pickupBtn');
    const deliveryBtn = document.getElementById('deliveryBtn');
    
    pickupBtn?.addEventListener('click', () => selectReceiveMethod('pickup'));
    deliveryBtn?.addEventListener('click', () => selectReceiveMethod('delivery'));
    
    // Proceed to payment button
    const proceedBtn = document.getElementById('proceedPaymentBtn');
    proceedBtn?.addEventListener('click', handleProceedToPayment);
    
    // Time input validation
    const startTimeSelect = document.getElementById('startTime');
    const endTimeSelect = document.getElementById('endTime');
    
    startTimeSelect?.addEventListener('change', () => validateTimeRange());
    endTimeSelect?.addEventListener('change', () => validateTimeRange());
}

function selectReceiveMethod(method) {
    const pickupBtn = document.getElementById('pickupBtn');
    const deliveryBtn = document.getElementById('deliveryBtn');
    
    // Remove active class from all buttons
    pickupBtn?.classList.remove('active');
    deliveryBtn?.classList.remove('active');
    
    // Add active class to selected button
    if (method === 'pickup') {
        pickupBtn?.classList.add('active');
    } else {
        deliveryBtn?.classList.add('active');
    }
    
    // Update UI based on selection
    updateReceiveMethodUI(method);
}

function updateReceiveMethodUI(method) {
    // You can add logic here to show/hide different sections based on method
    console.log(`Selected receive method: ${method}`);
}

function validateTimeRange() {
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    
    if (startTime && endTime) {
        const start = new Date(`2000-01-01T${startTime}`);
        const end = new Date(`2000-01-01T${endTime}`);
        
        if (start >= end) {
            showNotification('End time must be after start time', 'error');
            document.getElementById('endTime').value = '';
        }
    }
}

function loadProductData(productId, quantity) {
    // The product data is already populated from the server-side Blade template
    // We just need to update the price breakdown with the correct quantity
    const currentPriceElement = document.getElementById('currentPrice');
    if (currentPriceElement) {
        const unitPrice = parseFloat(currentPriceElement.textContent);
        updatePriceBreakdown(quantity, unitPrice);
    }
}

function populateProductInfo(data, quantity) {
    // Product header
    document.getElementById('productName').textContent = data.name;
    document.getElementById('bakeryName').textContent = data.bakery;
    
    // Product image
    document.getElementById('productImage').src = data.image;
    document.getElementById('productImage').alt = data.name;
    
    // Pricing
    document.getElementById('currentPrice').textContent = data.currentPrice.toFixed(2);
    document.getElementById('originalPrice').textContent = data.originalPrice.toFixed(2);
    
    // Show/hide discount badge
    const discountBadge = document.getElementById('discountBadge');
    if (data.discount > 0) {
        discountBadge.textContent = `${data.discount}% off`;
        discountBadge.style.display = 'block';
    } else {
        discountBadge.style.display = 'none';
    }
    
    // Product details
    document.getElementById('location').textContent = data.location;
    document.getElementById('pickupOption').textContent = data.pickupOption;
    document.getElementById('expiryDate').textContent = `Expiry Date: ${data.expiryDate}`;
    document.getElementById('operatingHours').textContent = data.operatingHours;
    
    // Bakery contact details
    document.getElementById('bakeryContactName').textContent = data.bakeryContact;
    document.getElementById('bakeryAddress').textContent = data.bakeryAddress;
    document.getElementById('bakeryPhone').textContent = data.bakeryPhone;
    
    // Update price breakdown
    updatePriceBreakdown(quantity, data.currentPrice);
}

function updatePriceBreakdown(quantity, unitPrice) {
    const totalPrice = quantity * unitPrice;
    
    document.getElementById('itemQuantity').textContent = quantity;
    document.getElementById('itemPrice').textContent = `₱ ${unitPrice.toFixed(2)}`;
    document.getElementById('totalPrice').textContent = `₱ ${totalPrice.toFixed(2)}`;
}

function handleProceedToPayment() {
    // Validate form
    if (!validateForm()) {
        return;
    }
    
    // Get form data
    const orderData = collectOrderData();
    
    // In a real app, this would submit the order to the server
    console.log('Order data:', orderData);
    
    // Show success message
    showNotification('Redirecting to payment...', 'success');
    
    // Redirect to payment options page with order data
    setTimeout(() => {
        const urlParams = new URLSearchParams();
        urlParams.set('id', orderData.productId);
        urlParams.set('quantity', orderData.quantity);
        urlParams.set('method', orderData.receiveMethod);
        urlParams.set('phone', orderData.phoneNumber);
        urlParams.set('startTime', orderData.startTime);
        urlParams.set('endTime', orderData.endTime);
        
        window.location.href = '/consumer/payment-options?' + urlParams.toString();
    }, 1500);
}

function validateForm() {
    const phoneNumber = document.getElementById('phoneNumber').value.trim();
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    
    if (!phoneNumber) {
        showNotification('Please enter your phone number', 'error');
        return false;
    }
    
    if (!startTime || !endTime) {
        showNotification('Please select both start and end times', 'error');
        return false;
    }
    
    // Validate phone number format (basic validation)
    const phoneRegex = /^[0-9]{10,11}$/;
    if (!phoneRegex.test(phoneNumber.replace(/\D/g, ''))) {
        showNotification('Please enter a valid phone number', 'error');
        return false;
    }
    
    return true;
}

function collectOrderData() {
    const receiveMethod = document.querySelector('.method-btn.active')?.dataset.method || 'pickup';
    const phoneNumber = document.getElementById('phoneNumber').value.trim();
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    
    return {
        productId: new URLSearchParams(window.location.search).get('id'),
        quantity: parseInt(new URLSearchParams(window.location.search).get('quantity')) || 1,
        receiveMethod: receiveMethod,
        phoneNumber: phoneNumber,
        startTime: startTime,
        endTime: endTime,
        timestamp: new Date().toISOString()
    };
}

// Notification function (you can replace this with your existing notification system)
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Style the notification
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '12px 20px',
        borderRadius: '8px',
        color: 'white',
        fontWeight: '600',
        zIndex: '10000',
        maxWidth: '300px',
        wordWrap: 'break-word'
    });
    
    // Set background color based on type
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6',
        warning: '#f59e0b'
    };
    notification.style.backgroundColor = colors[type] || colors.info;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Back button function
function goBack() {
    // Get the product ID from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    
    if (productId) {
        // Navigate back to the specific product detail page
        window.location.href = `/consumer/food-detail/${productId}`;
    } else {
        // Fallback to browser back or food listing page
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '/consumer/food-listing';
        }
    }
}

// Map initialization
function initializeMap() {
    const mapElement = document.getElementById('map');
    if (!mapElement) return;
    
    const address = mapElement.getAttribute('data-address') || '';
    const name = mapElement.getAttribute('data-name') || 'Location';
    
    console.log('Initializing map with address:', address);
    
    // Initialize map container first (without setting view)
    const map = L.map('map');
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Try to geocode the address first, then set map view
    if (address && address !== 'Location not specified') {
        geocodeAddress(address, map, name);
    } else {
        // Default coordinates (Philippines center)
        const defaultLat = 12.8797;
        const defaultLng = 121.7740;
        map.setView([defaultLat, defaultLng], 6);
        L.marker([defaultLat, defaultLng])
            .addTo(map)
            .bindPopup(`<b>${name}</b><br>${address || 'Location'}`)
            .openPopup();
    }
}

// Geocode address using Nominatim API
function geocodeAddress(address, map, name) {
    console.log('Geocoding address:', address);
    
    // Check if address mentions Cebu
    const isCebu = address.toLowerCase().includes('cebu');
    const isManila = address.toLowerCase().includes('manila');
    
    // Clean address - remove Plus Codes (format: XXXX+XX) and other non-standard formats
    let cleanedAddress = address
        .replace(/\b[A-Z0-9]{4}\+[A-Z0-9]{2,3}\b/g, '') // Remove Plus Codes like "8V2C+H87"
        .replace(/^\s*,\s*/, '') // Remove leading comma
        .replace(/\s+/g, ' ') // Normalize whitespace
        .trim();
    
    // Build search query - prioritize city-specific searches
    let searchQuery = cleanedAddress;
    
    // If Cebu is mentioned, ensure we search specifically for Cebu City
    if (isCebu) {
        // Extract street name (usually the first part after removing Plus Code)
        const parts = cleanedAddress.split(',').map(p => p.trim()).filter(p => p);
        
        // Find street name (usually contains "St", "Street", "Ave", "Avenue", etc.)
        const streetPart = parts.find(p => 
            /\b(st|street|ave|avenue|road|rd|blvd|boulevard|drive|dr)\b/i.test(p)
        ) || parts[0] || cleanedAddress;
        
        // Build query with Cebu City context
        if (streetPart && streetPart.toLowerCase().includes('katipunan')) {
            searchQuery = `Katipunan Street, Cebu City, Cebu, Philippines`;
        } else {
            searchQuery = `${streetPart}, Cebu City, Cebu, Philippines`;
        }
    } else if (!cleanedAddress.toLowerCase().includes('philippines')) {
        // Add Philippines if not present
        searchQuery = `${cleanedAddress}, Philippines`;
    }
    
    console.log('Cleaned address:', cleanedAddress);
    console.log('Search query:', searchQuery);
    
    // Use Nominatim API for geocoding with country code restriction
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&countrycodes=ph&limit=10&addressdetails=1`;
    
    fetch(url, {
        headers: {
            'User-Agent': 'SavEats Application'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Geocoding results:', data);
        
        if (data && data.length > 0) {
            let bestMatch = null;
            
            // If address mentions Cebu, STRICTLY filter for Cebu results
            if (isCebu) {
                const cebuResults = data.filter(result => {
                    const displayName = (result.display_name || '').toLowerCase();
                    const address = result.address || {};
                    const city = (address.city || '').toLowerCase();
                    const town = (address.town || '').toLowerCase();
                    const state = (address.state || '').toLowerCase();
                    const province = (address.province || '').toLowerCase();
                    
                    return displayName.includes('cebu') || 
                           city.includes('cebu') || 
                           town.includes('cebu') || 
                           state.includes('cebu') ||
                           province.includes('cebu');
                });
                
                if (cebuResults.length > 0) {
                    bestMatch = cebuResults[0];
                    console.log('Found Cebu match:', bestMatch.display_name);
                } else {
                    console.warn('No Cebu matches found, using first result but it may be incorrect');
                    bestMatch = data[0];
                }
            } 
            // If address mentions Manila, filter for Manila results
            else if (isManila) {
                const manilaResults = data.filter(result => {
                    const displayName = (result.display_name || '').toLowerCase();
                    const address = result.address || {};
                    const city = (address.city || '').toLowerCase();
                    const town = (address.town || '').toLowerCase();
                    
                    return displayName.includes('manila') || 
                           city.includes('manila') || 
                           town.includes('manila');
                });
                
                if (manilaResults.length > 0) {
                    bestMatch = manilaResults[0];
                } else {
                    bestMatch = data[0];
                }
            } 
            // Otherwise use first result
            else {
                bestMatch = data[0];
            }
            
            if (bestMatch) {
                const lat = parseFloat(bestMatch.lat);
                const lng = parseFloat(bestMatch.lon);
                
                console.log('Setting map to:', lat, lng, '-', bestMatch.display_name);
                
                // Set map view to geocoded location
                map.setView([lat, lng], 15);
                
                // Add marker
                L.marker([lat, lng])
                    .addTo(map)
                    .bindPopup(`<b>${name}</b><br>${address}`)
                    .openPopup();
            } else {
                // Fallback to city center based on detected city
                if (isCebu) {
                    console.warn('Using Cebu City center as fallback');
                    const cebuLat = 10.3157;
                    const cebuLng = 123.8854;
                    map.setView([cebuLat, cebuLng], 13);
                    L.marker([cebuLat, cebuLng])
                        .addTo(map)
                        .bindPopup(`<b>${name}</b><br>${address}<br><small>Location approximate</small>`)
                        .openPopup();
                } else {
                    console.warn('Using Philippines center as fallback');
                    const defaultLat = 12.8797;
                    const defaultLng = 121.7740;
                    map.setView([defaultLat, defaultLng], 6);
                    L.marker([defaultLat, defaultLng])
                        .addTo(map)
                        .bindPopup(`<b>${name}</b><br>${address}<br><small>Location approximate</small>`)
                        .openPopup();
                }
            }
        } else {
            // If geocoding fails, try a simpler search
            console.warn('No geocoding results, trying simpler search');
            
            if (isCebu) {
                // Try searching just for the street name in Cebu City
                const streetMatch = cleanedAddress.match(/\b([A-Za-z\s]+(?:St|Street|Ave|Avenue|Road|Rd|Blvd|Boulevard|Drive|Dr))\b/i);
                if (streetMatch) {
                    const streetName = streetMatch[1].trim();
                    const simpleQuery = `${streetName}, Cebu City, Cebu, Philippines`;
                    console.log('Trying simpler query:', simpleQuery);
                    
                    return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(simpleQuery)}&countrycodes=ph&limit=5&addressdetails=1`, {
                        headers: {
                            'User-Agent': 'SavEats Application'
                        }
                    })
                    .then(response => response.json())
                    .then(simpleData => {
                        if (simpleData && simpleData.length > 0) {
                            // Filter for Cebu results
                            const cebuResults = simpleData.filter(result => {
                                const displayName = (result.display_name || '').toLowerCase();
                                return displayName.includes('cebu');
                            });
                            
                            const bestMatch = cebuResults.length > 0 ? cebuResults[0] : simpleData[0];
                            const lat = parseFloat(bestMatch.lat);
                            const lng = parseFloat(bestMatch.lon);
                            
                            console.log('Found location with simpler query:', bestMatch.display_name);
                            map.setView([lat, lng], 15);
                            L.marker([lat, lng])
                                .addTo(map)
                                .bindPopup(`<b>${name}</b><br>${address}`)
                                .openPopup();
                        } else {
                            // Final fallback to Cebu City center
                            console.warn('Using Cebu City center as final fallback');
                            const cebuLat = 10.3157;
                            const cebuLng = 123.8854;
                            map.setView([cebuLat, cebuLng], 13);
                            L.marker([cebuLat, cebuLng])
                                .addTo(map)
                                .bindPopup(`<b>${name}</b><br>${address}<br><small>Location approximate - Cebu City</small>`)
                                .openPopup();
                        }
                    })
                    .catch(error => {
                        console.error('Simple geocoding error:', error);
                        // Use Cebu City center
                        const cebuLat = 10.3157;
                        const cebuLng = 123.8854;
                        map.setView([cebuLat, cebuLng], 13);
                        L.marker([cebuLat, cebuLng])
                            .addTo(map)
                            .bindPopup(`<b>${name}</b><br>${address}<br><small>Location approximate - Cebu City</small>`)
                            .openPopup();
                    });
                } else {
                    // No street name found, use Cebu City center
                    console.warn('No street name found, using Cebu City center');
                    const cebuLat = 10.3157;
                    const cebuLng = 123.8854;
                    map.setView([cebuLat, cebuLng], 13);
                    L.marker([cebuLat, cebuLng])
                        .addTo(map)
                        .bindPopup(`<b>${name}</b><br>${address}<br><small>Location approximate - Cebu City</small>`)
                        .openPopup();
                }
            } else if (isManila) {
                const manilaLat = 14.5995;
                const manilaLng = 120.9842;
                map.setView([manilaLat, manilaLng], 13);
                L.marker([manilaLat, manilaLng])
                    .addTo(map)
                    .bindPopup(`<b>${name}</b><br>${address}<br><small>Location approximate</small>`)
                    .openPopup();
            } else {
                const defaultLat = 12.8797;
                const defaultLng = 121.7740;
                map.setView([defaultLat, defaultLng], 6);
                L.marker([defaultLat, defaultLng])
                    .addTo(map)
                    .bindPopup(`<b>${name}</b><br>${address}<br><small>Location approximate</small>`)
                    .openPopup();
            }
        }
    })
    .catch(error => {
        console.error('Geocoding error:', error);
        // On error, use city-specific fallback
        const addressLower = address.toLowerCase();
        const isCebuError = addressLower.includes('cebu');
        
        if (isCebuError) {
            const cebuLat = 10.3157;
            const cebuLng = 123.8854;
            map.setView([cebuLat, cebuLng], 13);
            L.marker([cebuLat, cebuLng])
                .addTo(map)
                .bindPopup(`<b>${name}</b><br>${address}<br><small>Location approximate</small>`)
                .openPopup();
        } else {
            const defaultLat = 12.8797;
            const defaultLng = 121.7740;
            map.setView([defaultLat, defaultLng], 6);
            L.marker([defaultLat, defaultLng])
                .addTo(map)
                .bindPopup(`<b>${name}</b><br>${address}<br><small>Location approximate</small>`)
                .openPopup();
        }
    });
}

// Initialize page when DOM is loaded
console.log('Order Confirmation page initialized successfully!');
