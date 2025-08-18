@php
    $role = Auth::user()->role; // Make sure 'role' column exists on users table
@endphp

@if ($role === 'consumer')
    @include('components.sidebar.consumer')
@elseif ($role === 'establishment')
    @include('components.sidebar.establishment')
@elseif ($role === 'foodbank')
    @include('components.sidebar.foodbank')
@elseif ($role === 'admin')
    @include('components.sidebar.admin')
@else
    <p>No sidebar available for your role.</p>
@endif
