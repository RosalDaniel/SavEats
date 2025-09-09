@extends('layouts.establishment')

@section('title', 'Products Management - SavEats Establishment')

@section('header', 'Products & Listings')

@section('content')
<div class="products-management">
    <div class="products-header">
        <div class="header-actions">
            <button class="btn-primary">Add New Product</button>
            <button class="btn-secondary">Bulk Upload</button>
            <button class="btn-secondary">Export List</button>
        </div>
        <div class="filter-controls">
            <select class="filter-select">
                <option value="">All Categories</option>
                <option value="vegetables">Vegetables</option>
                <option value="fruits">Fruits</option>
                <option value="bakery">Bakery Items</option>
                <option value="dairy">Dairy Products</option>
                <option value="meat">Meat & Poultry</option>
            </select>
            <select class="filter-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="sold">Sold Out</option>
                <option value="expired">Expired</option>
                <option value="donated">Donated</option>
            </select>
        </div>
    </div>

    <div class="products-grid">
        <div class="product-card">
            <div class="product-image">
                <img src="/images/vegetables.jpg" alt="Mixed Vegetables" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDIwMCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMTUwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik04NS41IDc1TDEwMCA2MEwxMTQuNSA3NUwxMDAgOTBMODUuNSA3NVoiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+'" />
                <div class="product-badge available">Available</div>
            </div>
            <div class="product-details">
                <h3>Mixed Vegetables Bundle</h3>
                <p class="product-description">Fresh seasonal vegetables, slightly imperfect but perfectly edible</p>
                <div class="product-info">
                    <span class="product-price">₱250</span>
                    <span class="product-original-price">₱400</span>
                    <span class="product-discount">37% off</span>
                </div>
                <div class="product-meta">
                    <span class="product-quantity">5kg available</span>
                    <span class="product-expiry">Expires: Jan 25, 2024</span>
                </div>
            </div>
            <div class="product-actions">
                <button class="btn-sm btn-primary">Edit</button>
                <button class="btn-sm btn-warning">Donate</button>
                <button class="btn-sm btn-danger">Remove</button>
            </div>
        </div>

        <div class="product-card">
            <div class="product-image">
                <img src="/images/bread.jpg" alt="Fresh Bread" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDIwMCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMTUwIiBmaWxsPSIjRkVGM0UyIi8+CjxjaXJjbGUgY3g9IjEwMCIgY3k9Ijc1IiByPSIyMCIgZmlsbD0iI0Y5NzMxNiIvPgo8L3N2Zz4='" />
                <div class="product-badge low-stock">Low Stock</div>
            </div>
            <div class="product-details">
                <h3>Fresh Bread Loaves</h3>
                <p class="product-description">Day-old bread, perfect for families and still delicious</p>
                <div class="product-info">
                    <span class="product-price">₱30</span>
                    <span class="product-original-price">₱50</span>
                    <span class="product-discount">40% off</span>
                </div>
                <div class="product-meta">
                    <span class="product-quantity">2 loaves left</span>
                    <span class="product-expiry">Expires: Jan 22, 2024</span>
                </div>
            </div>
            <div class="product-actions">
                <button class="btn-sm btn-primary">Edit</button>
                <button class="btn-sm btn-warning">Donate</button>
                <button class="btn-sm btn-danger">Remove</button>
            </div>
        </div>

        <div class="product-card">
            <div class="product-image">
                <img src="/images/fruits.jpg" alt="Seasonal Fruits" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDIwMCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMTUwIiBmaWxsPSIjRkVGMkY5Ii8+CjxjaXJjbGUgY3g9IjEwMCIgY3k9Ijc1IiByPSIyNSIgZmlsbD0iI0VDNDg5OSIvPgo8L3N2Zz4='" />
                <div class="product-badge sold">Sold Out</div>
            </div>
            <div class="product-details">
                <h3>Seasonal Fruits Mix</h3>
                <p class="product-description">Assorted fruits with minor blemishes, great taste guaranteed</p>
                <div class="product-info">
                    <span class="product-price">₱180</span>
                    <span class="product-original-price">₱300</span>
                    <span class="product-discount">40% off</span>
                </div>
                <div class="product-meta">
                    <span class="product-quantity">Sold out</span>
                    <span class="product-expiry">Was: Jan 23, 2024</span>
                </div>
            </div>
            <div class="product-actions">
                <button class="btn-sm btn-secondary" disabled>Edit</button>
                <button class="btn-sm btn-primary">Restock</button>
                <button class="btn-sm btn-danger">Archive</button>
            </div>
        </div>
    </div>

    <div class="products-summary">
        <h2>Inventory Summary</h2>
        <div class="summary-grid">
            <div class="summary-card">
                <h4>Active Listings</h4>
                <p class="summary-value">12</p>
                <span class="summary-change positive">+3 this week</span>
            </div>
            <div class="summary-card">
                <h4>Total Value</h4>
                <p class="summary-value">₱3,450</p>
                <span class="summary-change neutral">Current inventory</span>
            </div>
            <div class="summary-card">
                <h4>Items Expiring Soon</h4>
                <p class="summary-value">5</p>
                <span class="summary-change warning">Within 3 days</span>
            </div>
            <div class="summary-card">
                <h4>Donation Potential</h4>
                <p class="summary-value">₱850</p>
                <span class="summary-change info">Available for donation</span>
            </div>
        </div>
    </div>
</div>
@endsection
