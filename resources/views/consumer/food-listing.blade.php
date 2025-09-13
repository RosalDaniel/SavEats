@extends('layouts.consumer')

@section('title', 'Food Listing - SavEats')

@section('header', 'Food Listing')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/food-listing.css') }}">
@endsection

@section('content')
<!-- Search and Filter Section -->
<div class="search-filter-section">
    <div class="search-bar">
        <input type="text" class="search-input" placeholder="Search for food items..." id="searchInput">
        <button class="search-btn" id="searchBtn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
            </svg>
        </button>
    </div>
    
    <div class="filter-actions">
        <div class="category-filters">
            <button class="category-btn active" data-category="all">All</button>
            <button class="category-btn" data-category="grocery">Grocery</button>
            <button class="category-btn" data-category="bakery">Bakery</button>
            <button class="category-btn" data-category="restaurant">Restaurant</button>
        </div>
        
        <div class="price-range-dropdown">
            <button class="dropdown-btn" id="priceRangeBtn">
                Price Range
            </button>
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
        <div class="food-card" data-category="${food.category}">
            <div class="food-image" style="background-image: url('${food.image}')">
                <div class="discount-badge">${food.discount}% OFF</div>
            </div>
            <div class="food-info">
                <h3 class="food-name">${food.name}</h3>
                <p class="food-quantity">${food.quantity}</p>
                <div class="price-section">
                    <span class="current-price">₱${food.price.toFixed(2)}</span>
                    <span class="original-price">₱${food.original_price.toFixed(2)}</span>
                </div>
                <div class="card-actions">
                    <button class="btn btn-primary" onclick="orderFood(${food.id})">Order Now</button>
                    <button class="btn btn-secondary" onclick="viewDetails(${food.id})">View Details</button>
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
function orderFood(foodId) {
    const food = foodData.find(f => f.id === foodId);
    if (food) {
        // In a real app, this would make an API call to create an order
        showNotification(`Order placed for ${food.name}!`, 'success');
    }
}

// View details function
function viewDetails(foodId) {
    const food = foodData.find(f => f.id === foodId);
    if (food) {
        // In a real app, this would open a modal or navigate to details page
        showNotification(`Viewing details for ${food.name}`, 'info');
    }
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
        if (confirm('Are you sure you want to logout?')) {
            // In a real app, this would make a logout API call
            window.location.href = '/login';
        }
    } else if (page === 'orders') {
        window.location.href = '/consumer/my-orders';
    } else if (page === 'dashboard') {
        window.location.href = '/dashboard/consumer';
    }
    // Add other navigation cases as needed
}
</script>
@endsection
