@extends('layouts.app')

@section('title', 'Terms & Conditions | SavEats')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/cms-content.css') }}">
<link href="{{ asset('css/home.css') }}" rel="stylesheet">
<style>
    .cms-content-page {
        max-width: 900px;
        margin: 2rem auto;
        padding: 2rem;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .content-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #347928;
    }
    .content-header h1 {
        color: #347928;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }
    .content-version {
        color: #666;
        font-size: 0.9rem;
        margin: 0.25rem 0;
    }
    .content-date {
        color: #666;
        font-size: 0.9rem;
        margin: 0.25rem 0;
    }
    .content-body {
        line-height: 1.8;
        color: #333;
    }
    .cms-content {
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    .no-content {
        text-align: center;
        padding: 3rem;
        color: #999;
    }
</style>
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

