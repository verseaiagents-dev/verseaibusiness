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

        <!-- Agents -->
        <a href="{{ route('user.intents.index') }}" class="sidebar-nav-item {{ request()->routeIs('user.intents.*') ? 'active' : '' }}" title="{{ __('admin.agents') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-1a7 7 0 00-7-7H9a7 7 0 00-7 7v1h5m4-5a4 4 0 100-8 4 4 0 000 8z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.agents') }}</div>
        </a>

        <!-- Messages -->
        <div class="sidebar-nav-item" title="{{ __('admin.messages') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-6 8h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-4z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.messages') }}</div>
        </div>

        <!-- Events -->
        <div class="sidebar-nav-item" title="{{ __('admin.events') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 2v2M18 2v2M3 8h18M5 22h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.events') }}</div>
        </div>

        <!-- Training Data -->
        <div class="sidebar-nav-item" title="{{ __('admin.training') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c4.418 0 8 1.79 8 4v10c0 2.21-3.582 4-8 4s-8-1.79-8-4V7c0-2.21 3.582-4 8-4z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.training') }}</div>
        </div>

        <!-- Intent Management -->
        <a href="{{ route('user.intents.index') }}" class="sidebar-nav-item {{ request()->routeIs('user.intents.*') ? 'active' : '' }}" title="Niyet Yönetimi">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <div class="sidebar-tooltip">Niyet Yönetimi</div>
        </a>

        <!-- API Event Management -->
        <a href="{{ route('user.api-events.index') }}" class="sidebar-nav-item {{ request()->routeIs('user.api-events.*') ? 'active' : '' }}" title="API Event Yönetimi">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <div class="sidebar-tooltip">API Event Yönetimi</div>
        </a>

        <!-- Billing -->
        <div class="sidebar-nav-item" title="{{ __('admin.billing') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18M3 12h18m-2 8H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.billing') }}</div>
        </div>


    </nav>

    <!-- Settings -->
    <div class="sidebar-footer">
        <div class="sidebar-nav-item" title="{{ __('admin.settings') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.591 1.064c1.513-.947 3.43.97 2.483 2.483a1.724 1.724 0 001.064 2.591c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.064 2.591c.947 1.513-.97 3.43-2.483 2.483a1.724 1.724 0 00-2.591 1.064c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.591-1.064c-1.513.947-3.43-.97-2.483-2.483a1.724 1.724 0 00-1.064-2.591c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.064-2.591c-.947-1.513.97-3.43 2.483-2.483 1.512.947 3.43-.97 2.483-2.483z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.settings') }}</div>
        </div>
    </div>
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
 