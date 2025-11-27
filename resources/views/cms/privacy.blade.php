@php
    $layout = match(session('user_type', 'consumer')) {
        'establishment' => 'layouts.establishment',
        'foodbank' => 'layouts.foodbank',
        'admin' => 'layouts.admin',
        default => 'layouts.consumer',
    };
@endphp
@extends($layout)

@section('title', 'Privacy Policy | SavEats')

@section('header', 'Privacy Policy')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/cms-content.css') }}">
@endsection

@section('content')
<div class="cms-content-page">
    <div class="content-header">
        <h1>Privacy Policy</h1>
        @if(isset($privacy) && $privacy)
            @if($privacy->version)
                <p class="content-version">Version {{ $privacy->version }}</p>
            @endif
            @if($privacy->published_at)
                <p class="content-date">Last Updated: {{ $privacy->published_at->format('F d, Y') }}</p>
            @endif
        @endif
    </div>
    
    <div class="content-body">
        @if(isset($privacy) && $privacy)
            <div class="cms-content">
                {!! nl2br(e($privacy->content)) !!}
            </div>
        @else
            <div class="no-content">
                <p>Privacy Policy is not available at this time. Please check back later.</p>
            </div>
        @endif
    </div>
</div>
@endsection

