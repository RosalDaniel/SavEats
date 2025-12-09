// Order Confirmation Page JavaScript

// Global variables
let pickupMap = null;
let map = null; // Delivery map - MUST be global
let storeMarker = null;
let consumerMarker = null; // Always null by default, only created after address selection
let routeLine = null; // Red polyline
let currentMethod = 'pickup';
let geocodeCache = {};
let addressSearchTimeout = null; // For debouncing
let selectedSuggestionIndex = -1; // For keyboard navigation

// Make map accessible globally for debugging
window.deliveryMap = () => map;
window.deliveryStoreMarker = () => storeMarker;
window.deliveryConsumerMarker = () => consumerMarker;

// Test function to manually place consumer marker (for debugging)
window.testPlaceConsumerMarker = function(lat, lng) {
    if (!lat || !lng) {
        // Default test location near Cebu
        lat = 10.3157;
        lng = 123.8854 + 0.01; // Slightly east of store
    }
    console.log('Test: Placing consumer marker at', lat, lng);
    placeConsumerMarker(lat, lng);
};

// Cebu City coordinates (default store location)
const CEBU_CENTER_LAT = 10.3157;
const CEBU_CENTER_LNG = 123.8854;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the page
    initializeOrderConfirmation();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize maps
    initializePickupMap();
    
    // Wait for Google Maps API to load before initializing delivery
    if (typeof google !== 'undefined' && google.maps) {
        initializeDeliveryFeatures();
    } else {
        // Wait for Google Maps to load
        window.initDeliveryFeatures = initializeDeliveryFeatures;
    }
});

function initializeOrderConfirmation() {
    // Get the actual product price from the page
    const currentPriceElement = document.getElementById('currentPrice');
    const quantityElement = document.getElementById('itemQuantity');
    
    if (currentPriceElement && quantityElement) {
        const unitPrice = parseFloat(currentPriceElement.textContent.replace(/,/g, ''));
        const quantity = parseInt(quantityElement.textContent) || 1;
        updatePriceBreakdown(quantity, unitPrice, 0);
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
    
    // 2. Autocomplete Suggestions (Fully Functional)
    const addressInput = document.getElementById('deliveryAddress');
    if (addressInput) {
        const suggestionBox = document.getElementById('addressSuggestions');
        if (!suggestionBox) {
            // Create suggestion box if it doesn't exist
            const newSuggestionBox = document.createElement('div');
            newSuggestionBox.id = 'addressSuggestions';
            newSuggestionBox.classList.add('autocomplete-suggestions');
            addressInput.parentNode.appendChild(newSuggestionBox);
        }
        
        let debounceTimer = null;
        
        addressInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (!query) {
                const suggestions = document.getElementById('addressSuggestions');
                if (suggestions) suggestions.innerHTML = '';
                return;
            }
            
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                console.log('Searching for address:', query);
                // Use server-side proxy to avoid CORS issues
                fetch(`/consumer/geocode/address?q=${encodeURIComponent(query)}`)
                    .then(res => {
                        console.log('Geocode response status:', res.status);
                        if (!res.ok) {
                            throw new Error(`Network error: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Geocode response data:', data);
                        console.log('Response type:', typeof data, 'Is array:', Array.isArray(data));
                        
                        // Handle error response from proxy
                        if (data && typeof data === 'object' && data.error) {
                            console.error('Geocoding error:', data.error);
                            const suggestions = document.getElementById('addressSuggestions');
                            if (suggestions) {
                                suggestions.innerHTML = '';
                                suggestions.style.display = 'none';
                            }
                            if (data.error !== 'Rate limit exceeded. Please wait a moment.') {
                                showNotification('Address search temporarily unavailable. Please try again.', 'error');
                            }
                            return;
                        }
                        
                        const suggestions = document.getElementById('addressSuggestions');
                        if (!suggestions) {
                            console.error('Suggestions container not found');
                            return;
                        }
                        suggestions.innerHTML = '';
                        
                        // Check if data is an array
                        if (!Array.isArray(data)) {
                            console.error('Invalid response format, expected array:', typeof data, data);
                            suggestions.style.display = 'none';
                            return;
                        }
                        
                        if (!data || data.length === 0) {
                            console.log('No suggestions returned from API (empty array)');
                            // Show a helpful message with option to use current input as manual address
                            const noResults = document.createElement('div');
                            noResults.className = 'suggestion-item';
                            noResults.style.fontStyle = 'italic';
                            noResults.style.color = '#666';
                            noResults.style.padding = '12px';
                            noResults.innerHTML = 'No results found. <br><small>You can enter the address manually and the marker will be placed when you proceed.</small>';
                            suggestions.appendChild(noResults);
                            
                            // Add option to geocode current input as-is
                            if (query.length >= 5) {
                                const manualOption = document.createElement('div');
                                manualOption.className = 'suggestion-item';
                                manualOption.style.backgroundColor = '#f0f0f0';
                                manualOption.style.fontWeight = '600';
                                manualOption.textContent = `Use "${query}" as delivery address`;
                                manualOption.onclick = () => {
                                    // Try to geocode the full query one more time with different format
                                    fetch(`/consumer/geocode/address?q=${encodeURIComponent(query + ', Cebu City, Philippines')}`)
                                        .then(res => res.json())
                                        .then(geoData => {
                                            if (Array.isArray(geoData) && geoData.length > 0) {
                                                handleSuggestionClick(geoData[0], addressInput, suggestions);
                                            } else {
                                                // If still no results, allow manual entry
                                                addressInput.value = query;
                                                suggestions.innerHTML = '';
                                                suggestions.style.display = 'none';
                                                showNotification('Address saved. You can proceed with manual address entry.', 'info');
                                            }
                                        })
                                        .catch(() => {
                                            addressInput.value = query;
                                            suggestions.innerHTML = '';
                                            suggestions.style.display = 'none';
                                        });
                                };
                                suggestions.appendChild(manualOption);
                            }
                            
                            suggestions.style.display = 'block';
                            return;
                        }
                        
                        console.log('Found', data.length, 'suggestions');
                        console.log('Sample suggestion:', data[0]);
                        
                        data.forEach((place, index) => {
                            const item = document.createElement('div');
                            item.classList.add('suggestion-item');
                            item.textContent = place.display_name || place.name || 'Unknown location';
                            
                            // Use both onclick and addEventListener for maximum compatibility
                            const clickHandler = (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                console.log('Suggestion item clicked, index:', index);
                                handleSuggestionClick(place, addressInput, suggestions);
                            };
                            
                            item.onclick = clickHandler;
                            item.addEventListener('click', clickHandler, true);
                            item.addEventListener('mousedown', clickHandler, true);
                            
                            item.style.cursor = 'pointer';
                            item.style.userSelect = 'none';
                            
                            suggestions.appendChild(item);
                        });
                        
                        // Show suggestions box
                        suggestions.style.display = 'block';
                        console.log('Suggestions displayed, count:', data.length);
                    })
                    .catch(error => {
                        console.error('Address search error:', error);
                    });
            }, 300);
        });
        
        // Hide suggestions on blur
        addressInput.addEventListener('blur', () => {
            setTimeout(() => {
                const suggestions = document.getElementById('addressSuggestions');
                if (suggestions) suggestions.innerHTML = '';
            }, 200);
        });
    }
}

function selectReceiveMethod(method) {
    currentMethod = method;
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
    const pickupUI = document.getElementById('pickupUI');
    const deliveryUI = document.getElementById('deliveryUI');
    
    if (method === 'pickup') {
        // Show pickup UI, hide delivery UI
        if (pickupUI) pickupUI.style.display = 'block';
        if (deliveryUI) deliveryUI.style.display = 'none';
        
        // Hide delivery fee in price breakdown
        const deliveryFeeRow = document.getElementById('deliveryFeeRow');
        if (deliveryFeeRow) deliveryFeeRow.style.display = 'none';
        
        // Update total price (remove delivery fee)
        updatePriceBreakdown(
            parseInt(document.getElementById('itemQuantity').textContent) || 1,
            parseFloat(document.getElementById('currentPrice').textContent.replace(/,/g, '')),
            0
        );
    } else {
        // Show delivery UI, hide pickup UI
        if (pickupUI) pickupUI.style.display = 'none';
        if (deliveryUI) deliveryUI.style.display = 'block';
        
        // Initialize delivery map if not already initialized
        // Use a longer delay to ensure the container is fully visible and rendered
        setTimeout(() => {
            if (!map) {
                // Ensure the map container is visible before initializing
                const mapContainer = document.getElementById('deliveryMap');
                if (mapContainer && mapContainer.offsetParent !== null) {
                    initializeDeliveryMap();
                } else {
                    // Container not visible yet, retry after a short delay
                    setTimeout(() => {
                        if (!map) {
                            initializeDeliveryMap();
                        }
                    }, 200);
                }
            } else {
                // Map already exists, invalidate size to fix rendering after UI switch
                setTimeout(() => {
                    if (map) {
                        map.invalidateSize();
                        // Ensure map is centered on store and store marker is visible
                        if (storeMarker) {
                            const storePos = storeMarker.getLatLng();
                            map.setView([storePos.lat, storePos.lng], 15);
                            // Ensure store marker is still on the map (protect from being removed)
                            if (!map.hasLayer(storeMarker)) {
                                storeMarker.addTo(map);
                            }
                        }
                    }
                }, 100);
            }
        }, 150);
    }
}

function validateTimeRange() {
    const startTime = document.getElementById('startTime')?.value;
    const endTime = document.getElementById('endTime')?.value;
    
    if (startTime && endTime) {
        const start = new Date(`2000-01-01T${startTime}`);
        const end = new Date(`2000-01-01T${endTime}`);
        
        if (start >= end) {
            showNotification('End time must be after start time', 'error');
            document.getElementById('endTime').value = '';
        }
    }
}

function updatePriceBreakdown(quantity, unitPrice, deliveryFee = 0) {
    const subtotal = quantity * unitPrice;
    // Delivery fee is informational estimate only - NOT included in total
    const total = subtotal; // Total does NOT include delivery fee
    
    document.getElementById('itemQuantity').textContent = quantity;
    document.getElementById('itemPrice').textContent = `₱ ${unitPrice.toFixed(2)}`;
    
    // Update delivery fee row if delivery is selected (informational estimate only)
    if (currentMethod === 'delivery' && deliveryFee > 0) {
        const deliveryFeeRow = document.getElementById('deliveryFeeRow');
        const deliveryFeePrice = document.getElementById('deliveryFeePrice');
        if (deliveryFeeRow) deliveryFeeRow.style.display = 'flex';
        if (deliveryFeePrice) deliveryFeePrice.textContent = `₱ ${deliveryFee.toFixed(2)} (Estimate)`;
    } else {
        const deliveryFeeRow = document.getElementById('deliveryFeeRow');
        if (deliveryFeeRow) deliveryFeeRow.style.display = 'none';
    }
    
    document.getElementById('totalPrice').textContent = `₱ ${total.toFixed(2)}`;
}

// Pickup Map Initialization
function initializePickupMap() {
    const mapElement = document.getElementById('pickupMap');
    if (!mapElement) return;
    
    const address = mapElement.getAttribute('data-address') || '';
    const name = mapElement.getAttribute('data-name') || 'Location';
    
    // Initialize map
    pickupMap = L.map('pickupMap');
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(pickupMap);
    
    // Geocode address
    if (address && address !== 'Location not specified') {
        geocodeAddressForPickup(address, name);
    } else {
        const defaultLat = 12.8797;
        const defaultLng = 121.7740;
        pickupMap.setView([defaultLat, defaultLng], 6);
        L.marker([defaultLat, defaultLng])
            .addTo(pickupMap)
            .bindPopup(`<b>${name}</b><br>${address || 'Location'}`)
            .openPopup();
    }
}

function geocodeAddressForPickup(address, name) {
    const cacheKey = address.toLowerCase();
    if (geocodeCache[cacheKey]) {
        const { lat, lng } = geocodeCache[cacheKey];
        pickupMap.setView([lat, lng], 15);
        L.marker([lat, lng])
            .addTo(pickupMap)
            .bindPopup(`<b>${name}</b><br>${address}`)
            .openPopup();
        return;
    }
    
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address + ', Philippines')}&countrycodes=ph&limit=1&addressdetails=1`;
    
    fetch(url, {
        headers: {
            'User-Agent': 'SavEats Application'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);
            geocodeCache[cacheKey] = { lat, lng };
            
            pickupMap.setView([lat, lng], 15);
            L.marker([lat, lng])
                .addTo(pickupMap)
                .bindPopup(`<b>${name}</b><br>${address}`)
                .openPopup();
        }
    })
    .catch(error => {
        console.error('Geocoding error:', error);
    });
}

// Delivery Map and Features Initialization
function initializeDeliveryFeatures() {
    // Initialize Google Places Autocomplete if available
    const deliveryAddressInput = document.getElementById('deliveryAddress');
    if (deliveryAddressInput && typeof google !== 'undefined' && google.maps && google.maps.places) {
        try {
            addressAutocomplete = new google.maps.places.Autocomplete(deliveryAddressInput, {
                componentRestrictions: { country: 'ph' },
                fields: ['formatted_address', 'geometry', 'name']
            });
            
        addressAutocomplete.addListener('place_changed', () => {
            const place = addressAutocomplete.getPlace();
            if (place.geometry && place.geometry.location) {
                const lat = typeof place.geometry.location.lat === 'function' 
                    ? place.geometry.location.lat() 
                    : place.geometry.location.lat;
                const lng = typeof place.geometry.location.lng === 'function' 
                    ? place.geometry.location.lng() 
                    : place.geometry.location.lng;
                const address = place.formatted_address || place.name || '';
                
                if (isValidCoordinate(lat, lng)) {
                    setCustomerLocation(lat, lng, address);
                } else {
                    console.error('Invalid coordinates from Google Places:', lat, lng);
                    showNotification('Invalid location selected. Please try again.', 'error');
                }
            }
        });
        } catch (e) {
            console.warn('Google Places Autocomplete not available, using Nominatim only:', e);
        }
    }
}

// 1. Initialize Map + Store Marker + Variables
function initializeDeliveryMap() {
    const mapElement = document.getElementById('deliveryMap');
    if (!mapElement) {
        console.error('Delivery map container not found');
        return;
    }
    
    // Check if container is visible
    if (mapElement.offsetParent === null && mapElement.style.display === 'none') {
        console.warn('Map container is hidden, cannot initialize');
        return;
    }
    
    // Don't reinitialize if map already exists
    if (map) {
        console.warn('Delivery map already initialized');
        map.invalidateSize();
        return;
    }
    
    // Get store location from orderData (use stored pinned coordinates from establishment registration)
    const orderData = window.orderData || {};
    const storeName = orderData.establishmentName || 'Store';
    const establishmentAddress = orderData.establishmentAddress || 'Location not specified';
    
    // Use stored coordinates from establishment registration (pinned location)
    let storeLat = orderData.storeLat;
    let storeLng = orderData.storeLng;
    
    // Validate stored coordinates
    if (!storeLat || !storeLng || !isValidCoordinate(storeLat, storeLng)) {
        console.warn('Invalid stored coordinates, using Cebu center as fallback');
        storeLat = CEBU_CENTER_LAT;
        storeLng = CEBU_CENTER_LNG;
    }
    
    console.log('Using establishment stored coordinates:', storeLat, storeLng, 'for address:', establishmentAddress);
    
    try {
        // Initialize map centered on store location
        map = L.map('deliveryMap', {
            zoomControl: true
        }).setView([storeLat, storeLng], 15);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);
        
        // Establishment marker (fixed, not draggable) - RED marker
        storeMarker = L.marker(
            [storeLat, storeLng],
            { 
                draggable: false,
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }
        ).addTo(map).bindPopup(`<strong>${storeName}</strong><br>${establishmentAddress}`);
        
        console.log('Store marker created at:', storeLat, storeLng, 'for address:', establishmentAddress);
        
        // Ensure store marker cannot be dragged
        if (storeMarker.dragging) {
            storeMarker.dragging.disable();
        }
        
        // Initialize consumer marker and route line as null
        consumerMarker = null;
        routeLine = null;
        
        // Enable map click to set consumer location
        map.off('click'); // Remove any existing click handlers
        map.on('click', function(e) {
            console.log('Map clicked to set delivery location:', e.latlng);
            const clickedLat = e.latlng.lat;
            const clickedLng = e.latlng.lng;
            
            // Create or move consumer marker at clicked location
            if (consumerMarker) {
                // Move existing marker
                consumerMarker.setLatLng([clickedLat, clickedLng]);
                // Reverse geocode to get address
                reverseGeocode(clickedLat, clickedLng);
                // Update delivery data using OSRM (async)
                calculateDeliveryData(clickedLat, clickedLng).then(() => {
                    drawRoute();
                });
            } else {
                // Create new marker
                placeConsumerMarker(clickedLat, clickedLng);
            }
        });
        
        console.log('Map initialized. Store marker visible. Consumer marker will appear when address is selected or map is clicked.');
        
        // Hide delivery info initially
        const deliveryInfo = document.getElementById('deliveryInfo');
        if (deliveryInfo) deliveryInfo.style.display = 'none';
        
        // Invalidate size to ensure proper rendering
        setTimeout(() => {
            if (map) {
                map.invalidateSize();
            }
        }, 100);
        
        console.log('Delivery map initialized successfully at', storeLat, storeLng);
        console.log('Tip: Click on the map to set delivery location, or search for an address');
    } catch (error) {
        console.error('Error initializing delivery map:', error);
    }
}

// Function to geocode establishment address and place marker
function geocodeEstablishmentAddress(address, name) {
    const cacheKey = address.toLowerCase();
    
    // Check cache first
    if (geocodeCache[cacheKey]) {
        const { lat, lng } = geocodeCache[cacheKey];
        placeEstablishmentMarker(lat, lng, address, name);
        return;
    }
    
    // Geocode using Nominatim
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address + ', Philippines')}&countrycodes=ph&limit=1&addressdetails=1`;
    
    fetch(url, {
        headers: {
            'User-Agent': 'SavEats Application'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);
            
            // Cache the result
            geocodeCache[cacheKey] = { lat, lng };
            
            // Place the establishment marker
            placeEstablishmentMarker(lat, lng, address, name);
        } else {
            console.warn('Could not geocode establishment address, using default location');
            placeEstablishmentMarker(CEBU_CENTER_LAT, CEBU_CENTER_LNG, address, name);
        }
    })
    .catch(error => {
        console.error('Geocoding error:', error);
        // Fallback to default location
        placeEstablishmentMarker(CEBU_CENTER_LAT, CEBU_CENTER_LNG, address, name);
    });
}

// Function to place establishment marker on map
function placeEstablishmentMarker(lat, lng, address, name) {
    try {
        // Initialize map if not already initialized
        if (!map) {
            map = L.map('deliveryMap', {
                zoomControl: true
            }).setView([lat, lng], 15);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);
        } else {
            // Map already exists, just center on establishment
            map.setView([lat, lng], 15);
        }
        
        // Remove existing store marker if any
        if (storeMarker) {
            map.removeLayer(storeMarker);
        }
        
        // Create establishment marker (fixed, not draggable) - RED marker
        storeMarker = L.marker(
            [lat, lng],
            { 
                draggable: false,
                icon: L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41]
                })
            }
        ).addTo(map).bindPopup(`<strong>${name}</strong><br>${address}`);
        
        // Ensure store marker cannot be dragged
        if (storeMarker.dragging) {
            storeMarker.dragging.disable();
        }
        
        // Initialize consumer marker and route line as null
        consumerMarker = null;
        routeLine = null;
        
        // Enable map click to set consumer location
        map.off('click'); // Remove any existing click handlers
        map.on('click', function(e) {
            console.log('Map clicked to set delivery location:', e.latlng);
            const clickedLat = e.latlng.lat;
            const clickedLng = e.latlng.lng;
            
            // Create or move consumer marker at clicked location
            if (consumerMarker) {
                // Move existing marker
                consumerMarker.setLatLng([clickedLat, clickedLng]);
                // Reverse geocode to get address
                reverseGeocode(clickedLat, clickedLng);
                // Update delivery data using OSRM (async)
                calculateDeliveryData(clickedLat, clickedLng).then(() => {
                    drawRoute();
                });
            } else {
                // Create new marker
                placeConsumerMarker(clickedLat, clickedLng);
            }
        });
        
        // Hide delivery info initially
        const deliveryInfo = document.getElementById('deliveryInfo');
        if (deliveryInfo) deliveryInfo.style.display = 'none';
        
        // Invalidate size to ensure proper rendering
        setTimeout(() => {
            if (map) {
                map.invalidateSize();
            }
        }, 100);
        
        console.log('Establishment marker placed at:', lat, lng, 'for address:', address);
    } catch (error) {
        console.error('Error placing establishment marker:', error);
    }
}

// Helper function to validate coordinates
function isValidCoordinate(lat, lng) {
    // Check for null/undefined
    if (lat === null || lat === undefined || lng === null || lng === undefined) {
        return false;
    }
    
    // Check for NaN
    if (isNaN(lat) || isNaN(lng)) {
        return false;
    }
    
    // Check valid ranges
    if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
        return false;
    }
    
    // Check not (0,0) - which is in the ocean off Africa
    if (lat === 0 && lng === 0) {
        return false;
    }
    
    return true;
}

// Store marker is now created in initializeDeliveryMap()

// Helper function to handle suggestion click
function handleSuggestionClick(place, addressInput, suggestions) {
    console.log('handleSuggestionClick called with place:', place);
    
    // Try different possible field names for coordinates
    const lat = parseFloat(place.lat || place.latitude || place.y);
    const lon = parseFloat(place.lon || place.lng || place.longitude || place.x);
    
    console.log('Parsed coordinates - lat:', lat, 'lon:', lon);
    
    if (!isValidCoordinate(lat, lon)) {
        console.error('Invalid coordinates:', lat, lon, 'from place:', place);
        showNotification('Invalid location selected. Please try again.', 'error');
        return;
    }
    
    // Update address input
    const addressText = place.display_name || place.name || place.formatted_address || addressInput.value;
    addressInput.value = addressText;
    suggestions.innerHTML = '';
    suggestions.style.display = 'none';
    
    // Place consumer marker
    console.log('Coordinates valid, calling placeConsumerMarker with:', lat, lon);
    placeConsumerMarker(lat, lon);
}

function handleAddressInput(e) {
    const query = e.target.value.trim();
    
    // Clear previous timeout
    if (addressSearchTimeout) {
        clearTimeout(addressSearchTimeout);
    }
    
    if (query.length < 3) {
        const suggestions = document.getElementById('addressSuggestions');
        if (suggestions) suggestions.style.display = 'none';
        selectedSuggestionIndex = -1;
        return;
    }
    
    // Debounce: wait 300ms after user stops typing
    addressSearchTimeout = setTimeout(() => {
        searchAddressNominatim(query);
    }, 300);
}

function handleAddressKeydown(e) {
    const suggestionsDiv = document.getElementById('addressSuggestions');
    if (!suggestionsDiv || suggestionsDiv.style.display === 'none') {
        return;
    }
    
    const items = suggestionsDiv.querySelectorAll('.suggestion-item');
    if (items.length === 0) return;
    
    switch(e.key) {
        case 'ArrowDown':
            e.preventDefault();
            selectedSuggestionIndex = Math.min(selectedSuggestionIndex + 1, items.length - 1);
            updateSuggestionSelection(items);
            break;
        case 'ArrowUp':
            e.preventDefault();
            selectedSuggestionIndex = Math.max(selectedSuggestionIndex - 1, -1);
            updateSuggestionSelection(items);
            break;
        case 'Enter':
            e.preventDefault();
            if (selectedSuggestionIndex >= 0 && items[selectedSuggestionIndex]) {
                items[selectedSuggestionIndex].click();
            }
            break;
        case 'Escape':
            suggestionsDiv.style.display = 'none';
            selectedSuggestionIndex = -1;
            break;
    }
}

function updateSuggestionSelection(items) {
    items.forEach((item, index) => {
        if (index === selectedSuggestionIndex) {
            item.classList.add('selected');
            item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        } else {
            item.classList.remove('selected');
        }
    });
}

function searchAddressNominatim(query) {
    // Use server-side proxy to avoid CORS issues
    const url = `/consumer/geocode/address?q=${encodeURIComponent(query)}`;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Handle error response from proxy
        if (data.error) {
            console.error('Geocoding error:', data.error);
            if (data.error !== 'Rate limit exceeded. Please wait a moment.') {
                showNotification('Address search temporarily unavailable. Please try again.', 'error');
            }
            return;
        }
        displayAddressSuggestions(data);
    })
    .catch(error => {
        console.error('Address search error:', error);
        // Fallback: try direct Nominatim call (may fail due to CORS)
        const fallbackUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Philippines')}&countrycodes=ph&limit=5&addressdetails=1`;
        fetch(fallbackUrl, {
            headers: {
                'User-Agent': 'SavEats Application'
            }
        })
        .then(response => response.json())
        .then(data => displayAddressSuggestions(data))
        .catch(err => {
            console.error('Fallback geocoding also failed:', err);
            showNotification('Unable to search addresses. Please enter address manually.', 'error');
        });
    });
}

function displayAddressSuggestions(results) {
    const suggestionsDiv = document.getElementById('addressSuggestions');
    if (!suggestionsDiv) return;
    
    if (!results || results.length === 0) {
        suggestionsDiv.style.display = 'none';
        selectedSuggestionIndex = -1;
        return;
    }
    
    suggestionsDiv.innerHTML = '';
    selectedSuggestionIndex = -1;
    
    results.forEach((result, index) => {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.textContent = result.display_name;
        item.setAttribute('data-index', index);
        
        item.addEventListener('click', () => {
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);
            
            // Validate coordinates before setting location
            if (!isValidCoordinate(lat, lng)) {
                console.error('Invalid coordinates from Nominatim:', lat, lng);
                showNotification('Invalid location selected. Please try again.', 'error');
                return;
            }
            
            document.getElementById('deliveryAddress').value = result.display_name;
            suggestionsDiv.style.display = 'none';
            selectedSuggestionIndex = -1;
            placeConsumerMarker(lat, lng);
        });
        
        item.addEventListener('mouseenter', () => {
            selectedSuggestionIndex = index;
            updateSuggestionSelection(suggestionsDiv.querySelectorAll('.suggestion-item'));
        });
        
        suggestionsDiv.appendChild(item);
    });
    
    suggestionsDiv.style.display = 'block';
}

// 3. Create/Move Draggable Consumer Marker
function placeConsumerMarker(lat, lng) {
    console.log('placeConsumerMarker called with:', lat, lng);
    
    // Ensure map is initialized - if not, initialize it
    if (!map) {
        console.warn('Map not initialized, attempting to initialize...');
        initializeDeliveryMap();
        // Wait a bit for map to initialize
        setTimeout(() => {
            if (map) {
                placeConsumerMarker(lat, lng);
            } else {
                console.error('Failed to initialize map');
                showNotification('Map not ready. Please try again.', 'error');
            }
        }, 200);
        return;
    }
    
    if (!isValidCoordinate(lat, lng)) {
        console.error('Invalid coordinates:', lat, lng);
        showNotification('Invalid location coordinates. Please try again.', 'error');
        return;
    }
    
    // Remove existing consumer marker if it exists
    if (consumerMarker !== null) {
        try {
            map.removeLayer(consumerMarker);
        } catch (e) {
            console.warn('Error removing existing marker:', e);
        }
        consumerMarker = null;
    }
    
    // Remove existing route line
    if (routeLine !== null) {
        try {
            map.removeLayer(routeLine);
        } catch (e) {
            console.warn('Error removing existing route:', e);
        }
        routeLine = null;
    }
    
    try {
        // Create new draggable consumer marker with blue icon
        const blueIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
        
        consumerMarker = L.marker([lat, lng], { 
            draggable: true,
            icon: blueIcon
        });
        
        // Add marker to map
        consumerMarker.addTo(map);
        
        console.log('Consumer marker created and added to map:', consumerMarker);
        console.log('Marker position:', consumerMarker.getLatLng());
        console.log('Marker is on map:', map.hasLayer(consumerMarker));
        
        // Ensure dragging is enabled
        if (consumerMarker.dragging) {
            consumerMarker.dragging.enable();
            console.log('Dragging enabled on consumer marker');
        } else {
            // Force enable dragging if not available
            consumerMarker.draggable = true;
            console.warn('Dragging not available, set draggable to true');
        }
        
        // Bind popup
        consumerMarker.bindPopup("Delivery Location").openPopup();
        
        // Attach dragend event handler
        consumerMarker.off('dragend'); // Remove any existing handlers
        consumerMarker.on('dragend', function() {
            console.log('Consumer marker dragged to:', consumerMarker.getLatLng());
            updateAfterDrag();
        });
        
        // Map click handler is already set in initializeDeliveryMap()
        // It will create marker if it doesn't exist, or move it if it does
        
        // Fit map to show both markers
        if (storeMarker && consumerMarker) {
            const group = new L.featureGroup([storeMarker, consumerMarker]);
            const bounds = group.getBounds();
            map.fitBounds(bounds.pad(0.2));
            console.log('Map fitted to show both markers. Bounds:', bounds);
        } else {
            map.setView([lat, lng], 16);
            console.log('Map centered on consumer marker only');
        }
        
        // Invalidate size to ensure proper rendering
        setTimeout(() => {
            if (map) {
                map.invalidateSize();
                // Verify marker is still visible
                if (consumerMarker && map.hasLayer(consumerMarker)) {
                    console.log('Consumer marker verified visible on map');
                } else {
                    console.error('Consumer marker NOT visible on map!');
                }
            }
        }, 100);
        
        // Calculate delivery data and draw route (both use OSRM)
        // Calculate first, then draw route (both async)
        calculateDeliveryData(lat, lng).then(() => {
            drawRoute();
        });
        
        // Show delivery info
        const deliveryInfo = document.getElementById('deliveryInfo');
        if (deliveryInfo) deliveryInfo.style.display = 'block';
        
        console.log('Consumer marker successfully placed and configured');
    } catch (error) {
        console.error('Error creating consumer marker:', error);
        showNotification('Error placing delivery location. Please try again.', 'error');
    }
}

// 4. When Marker is Dragged → Update Everything
async function updateAfterDrag() {
    if (!consumerMarker || !map) return;
    
    const pos = consumerMarker.getLatLng();
    
    // Update hidden inputs
    const latInput = document.getElementById('deliveryLat');
    const lngInput = document.getElementById('deliveryLng');
    if (latInput) latInput.value = pos.lat;
    if (lngInput) lngInput.value = pos.lng;
    
    // Reverse geocode to update address
    reverseGeocode(pos.lat, pos.lng);
    
    // Recalculate delivery data using OSRM (async)
    await calculateDeliveryData(pos.lat, pos.lng);
    
    // Redraw route using OSRM geometry (async)
    await drawRoute();
}

function reverseGeocode(lat, lng) {
    // Validate coordinates before reverse geocoding
    if (!isValidCoordinate(lat, lng)) {
        console.error('Invalid coordinates for reverse geocoding:', lat, lng);
        return;
    }
    
    // Use server-side proxy
    const url = `/consumer/geocode/reverse?lat=${lat}&lng=${lng}`;
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data && data.display_name) {
            const addressInput = document.getElementById('deliveryAddress');
            if (addressInput) {
                // Always update address field with reverse geocoded address from pinned location
                // This ensures the typed address matches the pinned map location
                addressInput.value = data.display_name;
                console.log('Address updated from pinned location:', data.display_name);
            }
        }
    })
    .catch(error => {
        console.error('Reverse geocoding error:', error);
        // If reverse geocoding fails, keep the current address or use coordinates
        const addressInput = document.getElementById('deliveryAddress');
        if (addressInput && !addressInput.value.trim()) {
            addressInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
        // Fallback: try direct Nominatim call
        const fallbackUrl = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
        fetch(fallbackUrl, {
            headers: {
                'User-Agent': 'SavEats Application'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.display_name) {
                const addressInput = document.getElementById('deliveryAddress');
                if (addressInput) {
                    addressInput.value = data.display_name;
                }
            }
        })
        .catch(err => console.error('Fallback reverse geocoding also failed:', err));
    });
}

// 5. Get OSRM Route (driving distance and geometry)
async function getOSRMRoute(storeLat, storeLng, consumerLat, consumerLng) {
    try {
        // OSRM expects coordinates in [lng, lat] format
        const url = `https://router.project-osrm.org/route/v1/driving/${storeLng},${storeLat};${consumerLng},${consumerLat}?overview=full&geometries=geojson`;
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`OSRM API error: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.routes || data.routes.length === 0) {
            throw new Error('No route found');
        }
        
        const route = data.routes[0];
        const distanceInMeters = route.distance; // Distance in meters
        const geometry = route.geometry.coordinates; // Array of [lng, lat] pairs
        
        // Convert OSRM coordinates [lng, lat] to Leaflet format [lat, lng]
        const leafletCoordinates = geometry.map(coord => [coord[1], coord[0]]);
        
        return {
            distance: distanceInMeters / 1000, // Convert to kilometers
            coordinates: leafletCoordinates
        };
    } catch (error) {
        console.error('OSRM routing error:', error);
        // Fallback to straight-line distance if OSRM fails
        const storePos = L.latLng(storeLat, storeLng);
        const consumerPos = L.latLng(consumerLat, consumerLng);
        const fallbackDistance = storePos.distanceTo(consumerPos) / 1000;
        
        return {
            distance: fallbackDistance,
            coordinates: [[storeLat, storeLng], [consumerLat, consumerLng]]
        };
    }
}

// 5. Draw Red Route Line using OSRM geometry
async function drawRoute() {
    if (!consumerMarker || !storeMarker || !map) {
        // If consumer marker doesn't exist yet, don't draw route
        if (routeLine !== null) {
            map.removeLayer(routeLine);
            routeLine = null;
        }
        return;
    }
    
    const userPos = consumerMarker.getLatLng();
    const storePos = storeMarker.getLatLng();
    
    // Remove existing route line
    if (routeLine !== null) {
        map.removeLayer(routeLine);
        routeLine = null;
    }
    
    try {
        // Get OSRM route with real road geometry
        const routeData = await getOSRMRoute(
            storePos.lat, storePos.lng,
            userPos.lat, userPos.lng
        );
        
        // Draw route polyline using OSRM geometry coordinates
        routeLine = L.polyline(
            routeData.coordinates,
            {
                color: "red",
                weight: 4,
                opacity: 0.9
            }
        ).addTo(map);
        
        console.log('Route drawn using OSRM geometry with', routeData.coordinates.length, 'points');
    } catch (error) {
        console.error('Error drawing route:', error);
        // Fallback to straight line if OSRM fails
        routeLine = L.polyline(
            [
                [storePos.lat, storePos.lng],
                [userPos.lat, userPos.lng]
            ],
            {
                color: "red",
                weight: 4,
                opacity: 0.9
            }
        ).addTo(map);
    }
}

// 6. Distance, Fee, ETA Calculations using OSRM driving distance
async function calculateDeliveryData(lat, lng) {
    if (!storeMarker || !map) return;
    
    const store = storeMarker.getLatLng();
    
    // Show loading state
    const distanceEl = document.getElementById('deliveryDistance');
    const feeEl = document.getElementById('deliveryFeeDisplay');
    const etaEl = document.getElementById('deliveryETA');
    
    if (distanceEl) distanceEl.textContent = 'Calculating...';
    if (feeEl) feeEl.textContent = 'Calculating...';
    if (etaEl) etaEl.textContent = 'Calculating...';
    
    try {
        // Get OSRM route to calculate real driving distance
        const routeData = await getOSRMRoute(
            store.lat, store.lng,
            lat, lng
        );
        
        const distance = routeData.distance; // Distance in kilometers from OSRM
        
        // Fee calculation: ₱45 base + ₱15 per km (using OSRM driving distance)
        // Round up distance to next whole kilometer before calculating distance fee
        const roundedKm = Math.ceil(distance);
        const fee = 45 + (15 * roundedKm);
        const roundedFee = Math.round(fee * 100) / 100; // Round to 2 decimals
        
        // ETA based on driving distance
        const eta =
            distance < 3 ? "15–25 min" :
            distance < 7 ? "25–45 min" :
                           "45–60 min";
        
        // Save into hidden inputs
        const latInput = document.getElementById('deliveryLat');
        const lngInput = document.getElementById('deliveryLng');
        const distanceInput = document.getElementById('deliveryDistanceInput');
        const feeInput = document.getElementById('deliveryFee');
        const etaInput = document.getElementById('deliveryETAInput');
        
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
        if (distanceInput) distanceInput.value = distance.toFixed(2);
        // Delivery fee is informational estimate only - set to 0 in hidden input
        if (feeInput) feeInput.value = '0.00';
        if (etaInput) etaInput.value = eta;
        
        // Update UI display elements
        if (distanceEl) distanceEl.textContent = `${distance.toFixed(2)} km`;
        if (feeEl) feeEl.textContent = `₱ ${roundedFee.toFixed(2)} (Estimate)`;
        if (etaEl) etaEl.textContent = eta;
        
        // Update price breakdown
        const quantity = parseInt(document.getElementById('itemQuantity').textContent) || 1;
        const unitPrice = parseFloat(document.getElementById('currentPrice').textContent.replace(/,/g, ''));
        updatePriceBreakdown(quantity, unitPrice, roundedFee);
        
        console.log('Delivery data calculated using OSRM:', {
            distance: distance.toFixed(2) + ' km',
            fee: '₱' + roundedFee.toFixed(2),
            eta: eta
        });
    } catch (error) {
        console.error('Error calculating delivery data:', error);
        showNotification('Error calculating delivery distance. Using approximate distance.', 'warning');
        
        // Fallback to straight-line distance
        const fallbackDistance = store.distanceTo([lat, lng]) / 1000;
        // Round up distance to next whole kilometer before calculating distance fee
        const roundedKm = Math.ceil(fallbackDistance);
        const fee = 45 + (15 * roundedKm);
        const roundedFee = Math.round(fee * 100) / 100;
        const eta = fallbackDistance < 3 ? "15–25 min" : fallbackDistance < 7 ? "25–45 min" : "45–60 min";
        
        // Update inputs and UI with fallback values
        const latInput = document.getElementById('deliveryLat');
        const lngInput = document.getElementById('deliveryLng');
        const distanceInput = document.getElementById('deliveryDistanceInput');
        const feeInput = document.getElementById('deliveryFee');
        const etaInput = document.getElementById('deliveryETAInput');
        
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
        if (distanceInput) distanceInput.value = fallbackDistance.toFixed(2);
        // Delivery fee is informational estimate only - set to 0 in hidden input
        if (feeInput) feeInput.value = '0.00';
        if (etaInput) etaInput.value = eta;
        
        // Update UI display elements
        if (distanceEl) distanceEl.textContent = `${fallbackDistance.toFixed(2)} km`;
        if (feeEl) feeEl.textContent = `₱ ${roundedFee.toFixed(2)} (Estimate)`;
        if (etaEl) etaEl.textContent = eta;
        
        const quantity = parseInt(document.getElementById('itemQuantity').textContent) || 1;
        const unitPrice = parseFloat(document.getElementById('currentPrice').textContent.replace(/,/g, ''));
        updatePriceBreakdown(quantity, unitPrice, roundedFee);
    }
}

function handleProceedToPayment() {
    // Validate form
    if (!validateForm()) {
        return;
    }
    
    // Get form data
    let orderData;
    try {
        orderData = collectOrderData();
    } catch (error) {
        console.error('Error collecting order data:', error);
        showNotification(error.message || 'Error preparing order data. Please try again.', 'error');
        return;
    }
    
    // Show success message
    showNotification('Redirecting to payment...', 'success');
    
    // Redirect to payment options page with order data
    setTimeout(() => {
        const urlParams = new URLSearchParams();
        urlParams.set('id', orderData.productId);
        urlParams.set('quantity', orderData.quantity);
        urlParams.set('method', orderData.receiveMethod);
        urlParams.set('phone', orderData.phoneNumber);
        
        if (orderData.receiveMethod === 'pickup') {
        urlParams.set('startTime', orderData.startTime);
        urlParams.set('endTime', orderData.endTime);
        } else {
            // Delivery mode - encode all delivery data
            urlParams.set('deliveryAddress', encodeURIComponent(orderData.deliveryAddress));
            urlParams.set('deliveryLat', orderData.deliveryLat);
            urlParams.set('deliveryLng', orderData.deliveryLng);
            urlParams.set('deliveryDistance', orderData.deliveryDistance);
            // Delivery fee is informational estimate only - not passed to payment
            urlParams.set('deliveryETA', encodeURIComponent(orderData.deliveryETA));
            urlParams.set('deliveryInstructions', encodeURIComponent(orderData.deliveryInstructions));
            urlParams.set('fullName', encodeURIComponent(orderData.fullName));
        }
        
        window.location.href = '/consumer/payment-options?' + urlParams.toString();
    }, 1500);
}

function validateForm() {
    if (currentMethod === 'pickup') {
        const phoneNumber = document.getElementById('phoneNumber')?.value.trim();
        const startTime = document.getElementById('startTime')?.value;
        const endTime = document.getElementById('endTime')?.value;
    
    if (!phoneNumber) {
        showNotification('Please enter your phone number', 'error');
        return false;
    }
    
    if (!startTime || !endTime) {
        showNotification('Please select both start and end times', 'error');
        return false;
    }
    
        // Validate phone number format
        const cleaned = phoneNumber.replace(/\D/g, '');
        if (cleaned.length < 11 || cleaned.length > 12) {
            showNotification('Please enter a valid phone number (11 digits, format: 09123456789)', 'error');
            return false;
        }
        const phoneRegex = /^0\d{10}$/;
        if (!phoneRegex.test(cleaned)) {
            showNotification('Please enter a valid phone number (format: 09123456789)', 'error');
        return false;
    }
    
    return true;
    } else {
        // Delivery validation
        const fullName = document.getElementById('deliveryFullName')?.value.trim();
        const phoneNumber = document.getElementById('deliveryPhone')?.value.trim();
        const address = document.getElementById('deliveryAddress')?.value.trim();
        
        if (!fullName) {
            showNotification('Please enter your full name', 'error');
            return false;
        }
        
        if (!phoneNumber) {
            showNotification('Please enter your phone number', 'error');
            return false;
        }
        
        if (!address) {
            showNotification('Please enter a delivery address', 'error');
            return false;
        }
        
        if (!consumerMarker) {
            showNotification('Please select a delivery location by choosing an address from the suggestions', 'error');
            return false;
        }
        
        // Validate customer coordinates are valid
        const consumerPos = consumerMarker.getLatLng();
        if (!isValidCoordinate(consumerPos.lat, consumerPos.lng)) {
            showNotification('Invalid delivery location. Please select a valid address.', 'error');
            return false;
        }
        
        // Validate phone number format
        const cleaned = phoneNumber.replace(/\D/g, '');
        if (cleaned.length < 11 || cleaned.length > 12) {
            showNotification('Please enter a valid phone number (11 digits, format: 09123456789)', 'error');
            return false;
        }
        const phoneRegex = /^0\d{10}$/;
        if (!phoneRegex.test(cleaned)) {
            showNotification('Please enter a valid phone number (format: 09123456789)', 'error');
        return false;
    }
    
    return true;
    }
}

function collectOrderData() {
    const receiveMethod = currentMethod;
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    const quantity = parseInt(urlParams.get('quantity')) || 1;
    
    if (receiveMethod === 'pickup') {
    const phoneNumber = document.getElementById('phoneNumber').value.trim();
    const startTime = document.getElementById('startTime').value;
    const endTime = document.getElementById('endTime').value;
    
    return {
            productId: productId,
            quantity: quantity,
        receiveMethod: receiveMethod,
        phoneNumber: phoneNumber,
        startTime: startTime,
        endTime: endTime,
        timestamp: new Date().toISOString()
    };
    } else {
        // Delivery mode - validate customer location exists
        if (!consumerMarker || !storeMarker) {
            throw new Error('Customer location not set');
        }
        
        const consumerPos = consumerMarker.getLatLng();
        const storePos = storeMarker.getLatLng();
        
        if (!isValidCoordinate(consumerPos.lat, consumerPos.lng)) {
            throw new Error('Invalid customer coordinates');
        }
        
        const fullName = document.getElementById('deliveryFullName').value.trim();
        const phoneNumber = document.getElementById('deliveryPhone').value.trim();
        const instructions = document.getElementById('deliveryInstructions')?.value.trim() || '';
        
        // Use OSRM distance from hidden inputs (already calculated by calculateDeliveryData)
        const distanceInput = document.getElementById('deliveryDistanceInput');
        const etaInput = document.getElementById('deliveryETAInput');
        
        // Get values from hidden inputs (calculated using OSRM)
        const distance = distanceInput ? parseFloat(distanceInput.value) : (storePos.distanceTo(consumerPos) / 1000);
        // Delivery fee is informational estimate only - not used in order data
        const deliveryFee = '0.00'; // Always 0 - fee is estimate only, not included in order
        const eta = etaInput ? etaInput.value : (distance < 3 ? '15-25 min' : distance < 7 ? '25-45 min' : '45-60 min');
        
        // Use the address from the input field (which should match the pinned location)
        // The address field is automatically updated via reverse geocoding when marker is placed/moved
        // This ensures the address always matches the pinned map location
        const addressInput = document.getElementById('deliveryAddress');
        let address = addressInput ? addressInput.value.trim() : '';
        
        // If address field is empty or doesn't match marker, use reverse geocoded address from marker
        // The pinned map location (coordinates) is the source of truth
        if (!address || address === '') {
            // Fallback: use coordinates as address if reverse geocoding hasn't completed
            address = `${consumerPos.lat.toFixed(6)}, ${consumerPos.lng.toFixed(6)}`;
        }
        
        return {
            productId: productId,
            quantity: quantity,
            receiveMethod: receiveMethod,
            fullName: fullName,
            phoneNumber: phoneNumber,
            deliveryAddress: address, // Address from pinned location (reverse geocoded)
            deliveryLat: consumerPos.lat.toFixed(8), // Pinned location coordinates (source of truth)
            deliveryLng: consumerPos.lng.toFixed(8), // Pinned location coordinates (source of truth)
            deliveryDistance: distance.toFixed(2),
            deliveryFee: '0.00', // Delivery fee is informational estimate only - not included in order
            deliveryETA: eta,
            deliveryInstructions: instructions,
            timestamp: new Date().toISOString()
        };
    }
}

// Notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
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
    
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6',
        warning: '#f59e0b'
    };
    notification.style.backgroundColor = colors[type] || colors.info;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Back button function
function goBack() {
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    
    if (productId) {
        window.location.href = `/consumer/food-detail/${productId}`;
    } else {
        if (window.history.length > 1) {
            window.history.back();
        } else {
            window.location.href = '/consumer/food-listing';
        }
    }
}

// Initialize Google Maps when loaded (with retry)
function tryInitializeDeliveryFeatures() {
    if (typeof google !== 'undefined' && google.maps && google.maps.places) {
        initializeDeliveryFeatures();
    } else {
        // Retry after a delay
        setTimeout(tryInitializeDeliveryFeatures, 500);
    }
}

// Try to initialize immediately
tryInitializeDeliveryFeatures();

// Also try on window load
window.addEventListener('load', tryInitializeDeliveryFeatures);

console.log('Order Confirmation page initialized successfully!');

