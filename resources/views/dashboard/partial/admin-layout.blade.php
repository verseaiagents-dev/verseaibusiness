<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="light scroll-smooth group" data-layout="vertical" data-sidebar="light" data-sidebar-size="lg" data-mode="light" data-topbar="light" data-skin="default" data-navbar="sticky" data-content="fluid" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'VersAI Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="@yield('description', 'VersAI Admin Panel')" name="description">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('dashboard/assets/images/favicon.ico') }}">
    <script src="{{ asset('dashboard/assets/js/layout.js') }}"></script>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/tailwind2.css') }}">
    
    <!-- Custom Dashboard CSS - Load after Tailwind to override conflicts -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/custom-dashboard.css') }}">
    
    <!-- Tailwind CSS Fixes - Load last to ensure proper color preservation -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/tailwind-fixes.css') }}">
    
    @yield('additional-styles')
</head>

<body class="text-base bg-body-bg text-body font-public">
    <div class="dashboard-layout">
        <!-- Mobile Toggle Button -->
        <button id="mobileToggle" class="mobile-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <!-- Sidebar -->
        @include('dashboard.partial.admin-sidebar')

        <!-- Main Content Area -->
        <main class="dashboard-main" id="dashboardMain">
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('dashboard/assets/js/tailwick.bundle.js') }}"></script>
    <script src="{{ asset('dashboard/assets/js/app.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin page loaded');
            
            // Mobile toggle functionality
            const mobileToggle = document.getElementById('mobileToggle');
            const sidebar = document.querySelector('.dashboard-sidebar');
            
            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-open');
                });
            }
        });
    </script>
    
    @yield('additional-scripts')
</body>
</html> 