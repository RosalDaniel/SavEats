@php
    $layout = match(session('user_type', 'consumer')) {
        'establishment' => 'layouts.establishment',
        'foodbank' => 'layouts.foodbank',
        'admin' => 'layouts.admin',
        default => 'layouts.consumer',
    };
@endphp
@extends($layout)

@section('title', 'Terms & Conditions | SavEats')

@section('header', 'Terms & Conditions')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/cms-content.css') }}">
@endsection

@section('content')
<div class="cms-content-page">
    <div class="content-header">
        <h1>Terms & Conditions</h1>
        @if(isset($terms) && $terms)
            @if($terms->version)
                <p class="content-version">Version {{ $terms->version }}</p>
            @endif
            @if($terms->published_at)
                <p class="content-date">Last Updated: {{ $terms->published_at->format('F d, Y') }}</p>
            @endif
        @endif
    </div>
    
    <div class="content-body">
        @if(isset($terms) && $terms)
            <div class="cms-content">
                {!! nl2br(e($terms->content)) !!}
            </div>
        @else
            <div class="no-content">
                <p>Terms & Conditions are not available at this time. Please check back later.</p>
            </div>
        @endif
    </div>
</div>
@endsection

