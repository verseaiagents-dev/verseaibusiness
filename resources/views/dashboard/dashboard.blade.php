<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="light scroll-smooth group" data-layout="vertical" data-sidebar="light" data-sidebar-size="lg" data-mode="light" data-topbar="light" data-skin="default" data-navbar="sticky" data-content="fluid" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>VersAI Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="Minimal Admin & Dashboard Template" name="description">
    <meta content="Themesdesign" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('dashboard/assets/images/favicon.ico') }}">
    <script src="{{ asset('dashboard/assets/js/layout.js') }}"></script>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/tailwind2.css') }}">
    
    <!-- Custom Dashboard CSS - Load after Tailwind to override conflicts -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/custom-dashboard.css') }}">
    
    <!-- Tailwind CSS Fixes - Load last to ensure proper color preservation -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/tailwind-fixes.css') }}">
    
    <!-- Custom Modal Styles -->
    <style>
        .modal-overlay {
            backdrop-filter: blur(4px);
        }
        
        .modal-content {
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .project-card {
            transition: all 0.2s ease-in-out;
        }
        
        .project-card:hover {
            transform: translateY(-2px);
        }
        
        .dashboard-content {
            margin-bottom: 2rem;
        }
        
        .dashboard-content:last-child {
            margin-bottom: 0;
        }
    </style>
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
                        <h1 class="text-3xl font-bold text-gray-900">Son Durum</h1>
                        <p class="text-gray-600 mt-1">Welcome to your VersAI dashboard</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button id="refreshBtn" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Refresh Data
                        </button>
                        
                        <!-- Admin Panel Button (Only for admin users) -->
                        @if(auth()->check() && auth()->user()->role === 'admin')
                        <a href="{{ route('admin.panel') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zM10.5 15.75l-1.5-1.5L6 15.75" />
                            </svg>
                            Admin Panel
                        </a>
                        @endif
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

                <!-- Projects Section -->
                <div class="dashboard-content">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Projelerim</h2>
                            <p class="text-gray-600 mt-1">Oluşturduğunuz projeleri yönetin</p>
                        </div>
                        <button id="newProjectBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Yeni Proje
                        </button>
                    </div>

                    <!-- Projects Loading -->
                    <div id="projectsLoading" class="hidden mb-6">
                        <div class="flex items-center justify-center p-4 bg-blue-50 rounded-lg">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-3"></div>
                            <span class="text-blue-600 font-medium">Projeler yükleniyor...</span>
                        </div>
                    </div>

                    <!-- Projects List -->
                    <div id="projectsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Projects will be loaded here -->
                    </div>

                    <!-- Empty State -->
                    <div id="emptyProjects" class="hidden text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-gray-400 mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz proje oluşturmadınız</h3>
                        <p class="text-gray-600 mb-4">İlk projenizi oluşturarak başlayın</p>
                        <button id="createFirstProjectBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            İlk Projeyi Oluştur
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- New Project Modal -->
    <div id="newProjectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden modal-overlay">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl modal-content">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 flex-shrink-0">
                    <h3 class="text-lg font-semibold text-gray-900">Yeni Proje Oluştur</h3>
                    <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form id="newProjectForm" class="p-6 flex-1 overflow-y-auto">
                    <div class="space-y-4">
                        <!-- Project Name -->
                        <div>
                            <label for="projectName" class="block text-sm font-medium text-gray-700 mb-2">
                                Proje İsmi *
                            </label>
                            <input type="text" id="projectName" name="name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Proje ismini girin">
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="projectDescription" class="block text-sm font-medium text-gray-700 mb-2">
                                Açıklama
                            </label>
                            <textarea id="projectDescription" name="description" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Proje açıklaması (opsiyonel)"></textarea>
                        </div>

                        <!-- Token Limit -->
                        <div>
                            <label for="tokenLimit" class="block text-sm font-medium text-gray-700 mb-2">
                                Token Limit *
                            </label>
                            <div class="flex items-center space-x-2">
                                <input type="number" id="tokenLimit" name="token_limit" required min="1"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Token limit">
                                <span class="text-sm text-gray-500">token</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-500">
                                Mevcut bakiye: <span id="currentBalance" class="font-medium">--</span> token
                            </div>
                        </div>


                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200 bg-white flex-shrink-0">
                        <button type="button" id="cancelProjectBtn"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            İptal
                        </button>
                        <button type="submit" id="createProjectBtn"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Proje Oluştur
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
                this.loadProjects();
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

                // New Project Button
                const newProjectBtn = document.getElementById('newProjectBtn');
                if (newProjectBtn) {
                    newProjectBtn.addEventListener('click', () => {
                        this.openNewProjectModal();
                    });
                }

                // Create First Project Button
                const createFirstProjectBtn = document.getElementById('createFirstProjectBtn');
                if (createFirstProjectBtn) {
                    createFirstProjectBtn.addEventListener('click', () => {
                        this.openNewProjectModal();
                    });
                }

                // Modal event listeners
                this.bindModalEvents();

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

            bindModalEvents() {
                // Close modal button
                const closeModalBtn = document.getElementById('closeModalBtn');
                if (closeModalBtn) {
                    closeModalBtn.addEventListener('click', () => {
                        this.closeNewProjectModal();
                    });
                }

                // Cancel project button
                const cancelProjectBtn = document.getElementById('cancelProjectBtn');
                if (cancelProjectBtn) {
                    cancelProjectBtn.addEventListener('click', () => {
                        this.closeNewProjectModal();
                    });
                }

                // New project form
                const newProjectForm = document.getElementById('newProjectForm');
                if (newProjectForm) {
                    newProjectForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        this.createNewProject();
                    });
                }

                // Close modal when clicking outside
                const modal = document.getElementById('newProjectModal');
                if (modal) {
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) {
                            this.closeNewProjectModal();
                        }
                    });
                }

                // Close modal with ESC key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        const modal = document.getElementById('newProjectModal');
                        if (modal && !modal.classList.contains('hidden')) {
                            this.closeNewProjectModal();
                        }
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

            // Project Management Methods
            async loadProjects() {
                const projectsLoading = document.getElementById('projectsLoading');
                const projectsContainer = document.getElementById('projectsContainer');
                const emptyProjects = document.getElementById('emptyProjects');

                if (projectsLoading) projectsLoading.classList.remove('hidden');
                if (projectsContainer) projectsContainer.innerHTML = '';
                if (emptyProjects) emptyProjects.classList.add('hidden');

                try {
                    const response = await fetch('/api/projects', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        if (data.data.length === 0) {
                            if (emptyProjects) emptyProjects.classList.remove('hidden');
                        } else {
                            data.data.forEach(project => {
                                this.renderProjectCard(project);
                            });
                        }
                    } else {
                        console.error('Failed to load projects:', data.message);
                    }
                } catch (error) {
                    console.error('Error loading projects:', error);
                    if (emptyProjects) emptyProjects.classList.remove('hidden');
                } finally {
                    if (projectsLoading) projectsLoading.classList.add('hidden');
                }
            }

            renderProjectCard(project) {
                const projectsContainer = document.getElementById('projectsContainer');
                if (!projectsContainer) return;

                const projectCard = document.createElement('div');
                projectCard.className = 'bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow project-card';
                projectCard.innerHTML = `
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">${project.name}</h3>
                                <p class="text-gray-600 text-sm mb-3">${project.description || 'Açıklama yok'}</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="dashboardManager.editProject(${project.id})" 
                                    class="text-blue-600 hover:text-blue-800 p-1 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </button>
                                <button onclick="dashboardManager.deleteProject(${project.id})" 
                                    class="text-red-600 hover:text-red-800 p-1 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Token Limit:</span>
                                <span class="font-medium">${project.token_limit}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">LLM Model:</span>
                                <span class="font-medium">${this.getModelDisplayName(project.llm_model)}</span>
                            </div>
                            ${project.sector_agent_model ? `
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Sektör:</span>
                                <span class="font-medium">${project.sector_agent_model}</span>
                            </div>
                            ` : ''}
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Oluşturulma:</span>
                                <span class="font-medium">${new Date(project.created_at).toLocaleDateString('tr-TR')}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ${project.status === 'active' ? 'Aktif' : project.status === 'inactive' ? 'Pasif' : 'Arşivlenmiş'}
                                </span>
                            </div>
                            <button onclick="dashboardManager.goToKnowledgeBase(${project.id})" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                Knowledge Base
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ml-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                `;

                projectsContainer.appendChild(projectCard);
            }

            getModelDisplayName(modelKey) {
                const modelNames = {
                    'gpt-3.5-turbo': 'GPT-3.5 Turbo',
                    'gpt-4': 'GPT-4',
                    'claude-3-sonnet': 'Claude 3 Sonnet',
                    'claude-3-opus': 'Claude 3 Opus'
                };
                return modelNames[modelKey] || modelKey;
            }

            openNewProjectModal() {
                const modal = document.getElementById('newProjectModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    // Prevent body scroll when modal is open
                    document.body.style.overflow = 'hidden';
                    this.loadUserInfo();
                    this.resetForm();
                }
            }

            closeNewProjectModal() {
                const modal = document.getElementById('newProjectModal');
                if (modal) {
                    modal.classList.add('hidden');
                    // Restore body scroll when modal is closed
                    document.body.style.overflow = '';
                    this.resetForm();
                }
            }

            async loadUserInfo() {
                try {
                    const response = await fetch('/api/projects/user/info', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        const currentBalance = document.getElementById('currentBalance');
                        if (currentBalance) {
                            currentBalance.textContent = data.data.token_balance;
                        }
                    }
                } catch (error) {
                    console.error('Error loading user info:', error);
                }
            }

            resetForm() {
                const form = document.getElementById('newProjectForm');
                if (form) {
                    form.reset();
                }
            }

            async createNewProject() {
                const form = document.getElementById('newProjectForm');
                if (!form) return;

                const formData = new FormData(form);
                const projectData = {
                    name: formData.get('name'),
                    description: formData.get('description'),
                    token_limit: parseInt(formData.get('token_limit'))
                };

                // Validation
                if (!projectData.name) {
                    this.showError('Proje ismi gereklidir.');
                    return;
                }

                if (!projectData.token_limit || projectData.token_limit < 1) {
                    this.showError('Geçerli bir token limit giriniz.');
                    return;
                }

                const createBtn = document.getElementById('createProjectBtn');
                if (createBtn) {
                    createBtn.disabled = true;
                    createBtn.textContent = 'Oluşturuluyor...';
                }

                try {
                    const response = await fetch('/api/projects', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(projectData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showSuccess('Proje başarıyla oluşturuldu!');
                        this.closeNewProjectModal();
                        this.loadProjects(); // Reload projects list
                        this.loadDashboardData(); // Refresh dashboard stats
                        // Yeni proje oluşturulduktan sonra knowledge base sayfasına yönlendir
                        if (data.data && data.data.id) {
                            window.location.href = `/dashboard/knowledge-base/${data.data.id}`;
                        }
                    } else {
                        this.showError(data.message || 'Proje oluşturulamadı.');
                    }
                } catch (error) {
                    console.error('Error creating project:', error);
                    this.showError('Proje oluşturulurken bir hata oluştu.');
                } finally {
                    if (createBtn) {
                        createBtn.disabled = false;
                        createBtn.textContent = 'Proje Oluştur';
                    }
                }
            }

            async editProject(projectId) {
                // TODO: Implement edit project functionality
                this.showError('Düzenleme özelliği henüz aktif değil.');
            }

            async deleteProject(projectId) {
                if (!confirm('Bu projeyi silmek istediğinizden emin misiniz?')) {
                    return;
                }

                try {
                    const response = await fetch(`/api/projects/${projectId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showSuccess('Proje başarıyla silindi!');
                        this.loadProjects(); // Reload projects list
                        this.loadDashboardData(); // Refresh dashboard stats
                    } else {
                        this.showError(data.message || 'Proje silinemedi.');
                    }
                } catch (error) {
                    console.error('Error deleting project:', error);
                    this.showError('Proje silinirken bir hata oluştu.');
                }
            }

            goToKnowledgeBase(projectId) {
                // Navigate to knowledge base page
                window.location.href = `/dashboard/knowledge-base/${projectId}`;
            }

            showSuccess(message) {
                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                successDiv.textContent = message;
                
                document.body.appendChild(successDiv);
                
                setTimeout(() => {
                    successDiv.remove();
                }, 5000);
            }
        }

        // Make dashboardManager globally accessible
        const dashboardManager = new DashboardManager();

        // Initialize dashboard when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Use requestAnimationFrame for better performance
            requestAnimationFrame(() => {
                // dashboardManager.init(); // This line is now redundant as init is called in constructor
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
