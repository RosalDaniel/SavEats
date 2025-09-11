<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SaveEats - Fighting Food Waste, One Meal at a Time')</title>
    <link href="https://fonts.googleapis.com/css2?family=Afacad:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    @yield('styles')
</head>
<body>
    @unless(request()->routeIs('login') || request()->routeIs('registration'))
        @include('partials.header')
    @endunless
    
    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    @yield('scripts')
</body>
</html>
