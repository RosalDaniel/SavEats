@extends('layouts.admin')

@section('title', 'User Management - SavEats Admin')

@section('header', 'User Management')

@section('content')
<div class="users-management">
    <div class="users-header">
        <div class="search-bar">
            <input type="text" placeholder="Search users..." class="search-input">
            <button class="search-btn">🔍</button>
        </div>
        <div class="filter-controls">
            <select class="filter-select">
                <option value="">All Roles</option>
                <option value="consumer">Consumer</option>
                <option value="establishment">Establishment</option>
                <option value="foodbank">Food Bank</option>
            </select>
            <select class="filter-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
            </select>
        </div>
    </div>

    <div class="users-table">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">JD</div>
                            <span>John Doe</span>
                        </div>
                    </td>
                    <td>john@example.com</td>
                    <td><span class="role-badge consumer">Consumer</span></td>
                    <td><span class="status-badge active">Active</span></td>
                    <td>Jan 15, 2024</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-sm btn-primary">View</button>
                            <button class="btn-sm btn-warning">Edit</button>
                            <button class="btn-sm btn-danger">Suspend</button>
                        </div>
                    </td>
                </tr>
                <!-- More user rows would be dynamically generated -->
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <button class="page-btn">Previous</button>
        <span class="page-info">Page 1 of 10</span>
        <button class="page-btn">Next</button>
    </div>
</div>
@endsection
