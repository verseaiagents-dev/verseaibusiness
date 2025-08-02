<aside class="dashboard-sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <span>V</span>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- Overview -->
        <a href="{{ route('dashboard') }}" class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="{{ __('admin.overview') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4.5 10.5v9a1.5 1.5 0 001.5 1.5h12a1.5 1.5 0 001.5-1.5v-9" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.overview') }}</div>
        </a>



        <!-- Messages -->
        <div class="sidebar-nav-item" title="{{ __('admin.messages') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-6 8h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-4z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.messages') }}</div>
        </div>





        <!-- Billing -->
        <div class="sidebar-nav-item" title="{{ __('admin.billing') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18M3 12h18m-2 8H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.billing') }}</div>
        </div>

        <!-- Profile -->
        <a href="{{ route('profile.index') }}" class="sidebar-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" title="{{ __('admin.profile') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.profile') }}</div>
        </a>

        <!-- Admin Panel (Only for admin users) -->
        @if(auth()->check() && auth()->user()->role === 'admin')
        <a href="{{ route('admin.panel') }}" class="sidebar-nav-item admin-panel-item" title="{{ __('admin.admin_panel') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zM10.5 15.75l-1.5-1.5L6 15.75" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.admin_panel') }}</div>
        </a>
        @endif

    </nav>
</aside>

<style>
.admin-panel-item {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.admin-panel-item:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: scale(1.05);
}

.admin-panel-item svg {
    color: white;
}
</style>
 