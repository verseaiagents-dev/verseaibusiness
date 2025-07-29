<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="light scroll-smooth group" data-layout="vertical" data-sidebar="light" data-sidebar-size="lg" data-mode="light" data-topbar="light" data-skin="default" data-navbar="sticky" data-content="fluid" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>VersAI Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Minimal Admin & Dashboard Template" name="description">
    <meta content="Themesdesign" name="author">

    <link rel="shortcut icon" href="{{ asset('dashboard/assets/images/favicon.ico') }}">
    <script src="{{ asset('dashboard/assets/js/layout.js') }}"></script>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/tailwind2.css') }}">
    
    <!-- Custom Dashboard CSS - Load after Tailwind to override conflicts -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/custom-dashboard.css') }}">
    
    <!-- Tailwind CSS Fixes - Load last to ensure proper color preservation -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/tailwind-fixes.css') }}">
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
        @include('dashboard.partial.sidebar')

        <!-- Main Content Area -->
        <main class="dashboard-main" id="dashboardMain">
            <!-- Page Header -->
            <div class="dashboard-content">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                        <p class="text-gray-600 mt-1">Welcome to your VersAI dashboard</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button id="refreshBtn" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Refresh Data
                        </button>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            New Agent
                        </button>
                    </div>
                </div>

                <!-- Loading Indicator -->
                <div id="loadingIndicator" class="hidden mb-6">
                    <div class="flex items-center justify-center p-4 bg-blue-50 rounded-lg">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                        <span class="text-blue-600 font-medium">Loading dashboard data...</span>
                    </div>
                </div>

                <!-- Dashboard Stats -->
                <div id="statsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100">Total Agents</p>
                                <p id="totalAgents" class="text-3xl font-bold">--</p>
                            </div>
                            <div class="bg-blue-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-1a7 7 0 00-7-7H9a7 7 0 00-7 7v1h5m4-5a4 4 0 100-8 4 4 0 000 8z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">Active Sessions</p>
                                <p id="activeSessions" class="text-3xl font-bold">--</p>
                            </div>
                            <div class="bg-green-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100">Messages Today</p>
                                <p id="messagesToday" class="text-3xl font-bold">--</p>
                            </div>
                            <div class="bg-purple-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-6 8h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-4z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-orange-100">API Calls</p>
                                <p id="apiCalls" class="text-3xl font-bold">--</p>
                            </div>
                            <div class="bg-orange-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Enhanced JavaScript for better functionality -->
    <script>
        // Dashboard Data Management
        class DashboardManager {
            constructor() {
                this.statsData = {
                    totalAgents: 0,
                    activeSessions: 0,
                    messagesToday: 0,
                    apiCalls: 0
                };
                this.isLoading = false;
                this.init();
            }

            init() {
                this.bindEvents();
                this.loadDashboardData();
                this.startAutoRefresh();
            }

            bindEvents() {
                // Mobile sidebar toggle
                const mobileToggle = document.getElementById('mobileToggle');
                if (mobileToggle) {
                    mobileToggle.addEventListener('click', () => {
                        const sidebar = document.querySelector('.dashboard-sidebar');
                        if (sidebar) sidebar.classList.toggle('mobile-open');
                    });
                }

                // Refresh button
                const refreshBtn = document.getElementById('refreshBtn');
                if (refreshBtn) {
                    refreshBtn.addEventListener('click', () => {
                        this.loadDashboardData();
                    });
                }

                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', (e) => {
                    const sidebar = document.querySelector('.dashboard-sidebar');
                    const mobileToggle = document.getElementById('mobileToggle');
                    
                    if (window.innerWidth <= 768 && sidebar && mobileToggle) {
                        if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                            sidebar.classList.remove('mobile-open');
                        }
                    }
                });

                // Handle window resize with debouncing
                let resizeTimeout;
                window.addEventListener('resize', () => {
                    clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(() => {
                        const sidebar = document.querySelector('.dashboard-sidebar');
                        const main = document.getElementById('dashboardMain');
                        
                        if (window.innerWidth > 768) {
                            if (sidebar) sidebar.classList.remove('mobile-open');
                            if (main) main.classList.remove('full-width');
                        } else {
                            if (main) main.classList.add('full-width');
                        }
                    }, 100);
                });

                // Initialize sidebar state
                document.addEventListener('DOMContentLoaded', () => {
                    if (window.innerWidth <= 768) {
                        const main = document.getElementById('dashboardMain');
                        if (main) main.classList.add('full-width');
                    }
                });
            }

            showLoading() {
                this.isLoading = true;
                const loadingIndicator = document.getElementById('loadingIndicator');
                const statsContainer = document.getElementById('statsContainer');
                
                if (loadingIndicator) loadingIndicator.classList.remove('hidden');
                if (statsContainer) statsContainer.style.opacity = '0.5';
            }

            hideLoading() {
                this.isLoading = false;
                const loadingIndicator = document.getElementById('loadingIndicator');
                const statsContainer = document.getElementById('statsContainer');
                
                if (loadingIndicator) loadingIndicator.classList.add('hidden');
                if (statsContainer) statsContainer.style.opacity = '1';
            }

            async loadDashboardData() {
                if (this.isLoading) return;
                
                this.showLoading();
                
                try {
                    // Simulate API call with realistic data
                    const data = await this.fetchDashboardData();
                    this.updateStats(data);
                    this.hideLoading();
                } catch (error) {
                    console.error('Error loading dashboard data:', error);
                    this.hideLoading();
                    this.showError('Failed to load dashboard data');
                }
            }

            async fetchDashboardData() {
                // Simulate API delay (reduced for better performance)
                await new Promise(resolve => setTimeout(resolve, 500));
                
                // Generate realistic random data
                return {
                    totalAgents: Math.floor(Math.random() * 50) + 20,
                    activeSessions: Math.floor(Math.random() * 20) + 5,
                    messagesToday: Math.floor(Math.random() * 2000) + 500,
                    apiCalls: Math.floor(Math.random() * 10000) + 3000
                };
            }

            updateStats(data) {
                // Animate number changes
                this.animateNumber('totalAgents', data.totalAgents);
                this.animateNumber('activeSessions', data.activeSessions);
                this.animateNumber('messagesToday', data.messagesToday);
                this.animateNumber('apiCalls', data.apiCalls);
                
                this.statsData = data;
            }

            animateNumber(elementId, targetValue) {
                const element = document.getElementById(elementId);
                const currentValue = parseInt(element.textContent) || 0;
                
                // Skip animation if values are close
                if (Math.abs(targetValue - currentValue) < 10) {
                    element.textContent = targetValue.toLocaleString();
                    return;
                }
                
                const increment = (targetValue - currentValue) / 15;
                let current = currentValue;

                const timer = setInterval(() => {
                    current += increment;
                    if ((increment > 0 && current >= targetValue) || (increment < 0 && current <= targetValue)) {
                        current = targetValue;
                        clearInterval(timer);
                    }
                    element.textContent = Math.floor(current).toLocaleString();
                }, 80);
            }

            startAutoRefresh() {
                // Auto refresh every 60 seconds (reduced frequency)
                setInterval(() => {
                    this.loadDashboardData();
                }, 60000);
            }

            showError(message) {
                // Create error notification
                const errorDiv = document.createElement('div');
                errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                errorDiv.textContent = message;
                
                document.body.appendChild(errorDiv);
                
                // Remove after 5 seconds
                setTimeout(() => {
                    errorDiv.remove();
                }, 5000);
            }
        }

        // Initialize dashboard when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Use requestAnimationFrame for better performance
            requestAnimationFrame(() => {
                const dashboard = new DashboardManager();
            });
        });
    </script>

    <!-- Scripts -->
    <script src="{{ asset('dashboard/assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('dashboard/assets/libs/@popperjs/core/umd/popper.min.js') }}"></script>
    <script src="{{ asset('dashboard/assets/libs/tippy.js/tippy-bundle.umd.min.js') }}"></script>
    <script src="{{ asset('dashboard/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('dashboard/assets/libs/prismjs/prism.js') }}"></script>
    <script src="{{ asset('dashboard/assets/libs/lucide/umd/lucide.js') }}"></script>
    <script src="{{ asset('dashboard/assets/js/tailwick.bundle.js') }}"></script>
    <script src="{{ asset('dashboard/assets/js/app.js') }}"></script>

</body>
</html>
