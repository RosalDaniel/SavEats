@extends('layouts.consumer')

@section('title', 'Food Listing - SavEats')

@section('header', 'Food Listing')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/food-listing.css') }}">
@endsection

@section('content')
<!-- Enhanced Search and Filter Section -->
<div class="search-filter-section">
    <div class="search-header">
        <h2 class="search-title">Find Your Perfect Food</h2>
        <p class="search-subtitle">Discover amazing dishes from local restaurants and stores</p>
    </div>

    <div class="search-container">
        <div class="search-wrapper">
            <div class="search-input-group">
                <input 
                    type="text" 
                    class="search-input" 
                    placeholder="Search for delicious food items..." 
                    id="searchInput"
                >
                <button class="clear-btn" id="clearBtn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
                <button class="search-btn" id="searchBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="filter-section">
        <div class="filter-row">
            <!-- Desktop Categories -->
            <div class="category-filters">
                <div class="category-pill active" data-category="all">
                    <span>All Categories</span>
                </div>
                <div class="category-pill" data-category="fruits-vegetables">
                    <span>Fruits & Vegetables</span>
                </div>
                <div class="category-pill" data-category="baked-goods">
                    <span>Baked Goods</span>
                </div>
                <div class="category-pill" data-category="cooked-meals">
                    <span>Cooked Meals</span>
                </div>
                <div class="category-pill" data-category="packaged-goods">
                    <span>Packaged Goods</span>
                </div>
                <div class="category-pill" data-category="beverages">
                    <span>Beverages</span>
                </div>
            </div>

            <!-- Mobile Categories Toggle -->
            <div class="categories-toggle" id="categoriesToggle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"/>
                </svg>
                <span>Categories</span>
                <span class="toggle-arrow">▼</span>
            </div>

            <div class="price-dropdown">
                <div class="dropdown-trigger" id="priceDropdown">
                    <span>Price Range</span>
                    <span class="dropdown-arrow">▼</span>
                </div>
                <div class="dropdown-menu" id="priceMenu">
                    <div class="dropdown-item" data-price="all">All Prices</div>
                    <div class="dropdown-item" data-price="0-50">₱0 - ₱50</div>
                    <div class="dropdown-item" data-price="51-100">₱51 - ₱100</div>
                    <div class="dropdown-item" data-price="101+">₱101+</div>
                </div>
            </div>
        </div>

        <!-- Mobile Categories Grid -->
        <div class="categories-grid" id="categoriesGrid">
            <div class="mobile-category-item active" data-category="all">
                <div class="category-label">All Items</div>
            </div>
            <div class="mobile-category-item" data-category="fruits-vegetables">
                <div class="category-label">Fruits & Vegetables</div>
            </div>
            <div class="mobile-category-item" data-category="baked-goods">
                <div class="category-label">Baked Goods</div>
            </div>
            <div class="mobile-category-item" data-category="cooked-meals">
                <div class="category-label">Cooked Meals</div>
            </div>
            <div class="mobile-category-item" data-category="packaged-goods">
                <div class="category-label">Packaged Goods</div>
            </div>
            <div class="mobile-category-item" data-category="beverages">
                <div class="category-label">Beverages</div>
            </div>
        </div>

        <div class="active-filters" id="activeFilters">
            <!-- Active filter tags will appear here -->
        </div>
    </div>
</div>

<!-- Food Grid -->
<div class="food-grid" id="foodGrid">
    @foreach($foodListings as $food)
    <div class="food-card" data-category="{{ $food['category'] }}">
        <div class="food-image" style="background-image: url('{{ $food['image'] }}')">
            <div class="discount-badge">{{ $food['discount'] }}% OFF</div>
        </div>
        <div class="food-info">
            <h3 class="food-name">{{ $food['name'] }}</h3>
            <p class="food-quantity">{{ $food['quantity'] }}</p>
            <div class="price-section">
                <span class="current-price">₱{{ number_format($food['price'], 2) }}</span>
                <span class="original-price">₱{{ number_format($food['original_price'], 2) }}</span>
            </div>
            <div class="card-actions">
                <button class="btn btn-primary" onclick="orderFood({{ $food['id'] }})">Order Now</button>
                <button class="btn btn-secondary" onclick="viewDetails({{ $food['id'] }})">View Details</button>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Pagination -->
<div class="pagination" id="pagination">
    <button id="prevBtn" disabled>◀ Previous</button>
    <button class="active" data-page="1">1</button>
    <button data-page="2">2</button>
    <button data-page="3">3</button>
    <span>...</span>
    <button data-page="67">67</button>
    <button data-page="68">68</button>
    <button id="nextBtn">Next ▶</button>
</div>
@endsection

@section('scripts')
<script>
// Sample food data (in a real app, this would come from the server)
const foodData = @json($foodListings);

// Current state
let currentPage = 1;
let itemsPerPage = 12;
let currentCategory = 'grocery';
let filteredData = foodData;
let searchQuery = '';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    renderFoodGrid();
    setupEventListeners();
    updatePagination();
});

// Setup event listeners
function setupEventListeners() {
    // Mobile menu functionality is handled by consumer.js

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    
    searchInput.addEventListener('input', handleSearch);
    searchBtn.addEventListener('click', handleSearch);

    // Category filters
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', handleCategoryFilter);
    });

    // Pagination
    setupPaginationListeners();

    // Notification button
    const notificationBtn = document.getElementById('notificationBtn');
    notificationBtn?.addEventListener('click', () => {
        showNotification('No new notifications', 'info');
    });
}

// Mobile menu functions are handled by consumer.js

// Search functionality
function handleSearch() {
    const searchInput = document.getElementById('searchInput');
    searchQuery = searchInput.value.toLowerCase();
    applyFilters();
}

// Category filter functionality
function handleCategoryFilter(event) {
    // Remove active class from all buttons
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    event.target.classList.add('active');
    
    currentCategory = event.target.dataset.category;
    applyFilters();
}

// Apply filters and search
function applyFilters() {
    filteredData = foodData.filter(food => {
        const matchesSearch = food.name.toLowerCase().includes(searchQuery) || 
                            food.description.toLowerCase().includes(searchQuery);
        const matchesCategory = currentCategory === 'all' || food.category === currentCategory;
        return matchesSearch && matchesCategory;
    });
    
    currentPage = 1;
    renderFoodGrid();
    updatePagination();
}

// Render food grid
function renderFoodGrid() {
    const foodGrid = document.getElementById('foodGrid');
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageData = filteredData.slice(startIndex, endIndex);
    
    foodGrid.innerHTML = pageData.map(food => `
        <div class="food-card clickable" data-category="${food.category}" onclick="viewDetails(${food.id})">
            <div class="food-image" style="background-image: url('${food.image}')">
                <div class="discount-badge">${food.discount}% OFF</div>
            </div>
            <div class="food-info">
                <h3 class="food-name">${food.name}</h3>
                <p class="food-quantity">${food.quantity}</p>
                <div class="price-section">
                    <span class="current-price">₱${(food.price || 0).toFixed(2)}</span>
                    <span class="original-price">₱${(food.original_price || 0).toFixed(2)}</span>
                </div>
            </div>
        </div>
    `).join('');
}

// Pagination functions
function setupPaginationListeners() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const pageButtons = document.querySelectorAll('[data-page]');
    
    prevBtn?.addEventListener('click', () => changePage(currentPage - 1));
    nextBtn?.addEventListener('click', () => changePage(currentPage + 1));
    
    pageButtons.forEach(btn => {
        if (btn.dataset.page) {
            btn.addEventListener('click', () => changePage(parseInt(btn.dataset.page)));
        }
    });
}

function changePage(page) {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderFoodGrid();
        updatePagination();
    }
}

function updatePagination() {
    const totalPages = Math.ceil(filteredData.length / itemsPerPage);
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    // Update previous button
    if (prevBtn) {
        prevBtn.disabled = currentPage === 1;
    }
    
    // Update next button
    if (nextBtn) {
        nextBtn.disabled = currentPage === totalPages;
    }
    
    // Update page buttons
    document.querySelectorAll('.pagination [data-page]').forEach(btn => {
        if (btn.dataset.page) {
            const pageNum = parseInt(btn.dataset.page);
            btn.classList.toggle('active', pageNum === currentPage);
            btn.style.display = pageNum <= totalPages ? 'block' : 'none';
        }
    });
}

// Order food function
// orderFood function removed - entire card is now clickable for view details

// View details function
function viewDetails(foodId) {
    // Navigate to the product detail page
    window.location.href = `/consumer/food-detail/${foodId}`;
}

// Notification function
function showNotification(message, type = 'info') {
    // Simple notification - in a real app, you might use a toast library
    alert(message);
}

// Navigation function
function handleNavigation(event) {
    event.preventDefault();
    const page = event.target.dataset.page;
    
    if (page === 'logout') {
        // Direct logout without confirmation
        window.location.href = '/logout';
    } else if (page === 'orders') {
        window.location.href = '/consumer/my-orders';
    } else if (page === 'dashboard') {
        window.location.href = '/dashboard/consumer';
    }
    // Add other navigation cases as needed
}
</script>

<script src="{{ asset('js/food-listing.js') }}"></script>
@endsection
