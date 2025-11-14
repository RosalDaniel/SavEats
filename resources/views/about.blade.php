@extends('layouts.app')

@section('title', 'About Us - SaveEats')

@section('styles')
    <link href="{{ asset('css/home.css') }}" rel="stylesheet">
    <link href="{{ asset('css/about.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="main-content">
        <h1 class="page-title">About Us</h1>

        <section class="team-section">
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-avatar"></div>
                    <h3 class="member-name">Marson, Jomari C.</h3>
                    <p class="member-role">Project Manager</p>
                </div>

                <div class="team-member">
                    <div class="member-avatar"></div>
                    <h3 class="member-name">Magsayo, Henry Ken T.</h3>
                    <p class="member-role">QA Tester & Database Designer</p>
                </div>

                <div class="team-member">
                    <div class="member-avatar"></div>
                    <h3 class="member-name">Rosal, Mary Daniel P.</h3>
                    <p class="member-role">Programmer</p>
                </div>

                <div class="team-member">
                    <div class="member-avatar"></div>
                    <h3 class="member-name">Napisa, Marianne Joy Y.</h3>
                    <p class="member-role">Technical Writer & UI/UX Designer</p>
                </div>
            </div>
        </section>

        <section class="mission-statement">
            <blockquote class="mission-quote">
                "We are deeply passionate about fighting food waste and improving food security in our communities. Together, we can turn waste into opportunity and ensure no one goes hungry while good food is thrown away."
            </blockquote>
        </section>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/home.js') }}"></script>
    <script src="{{ asset('js/about.js') }}"></script>
@endsection
