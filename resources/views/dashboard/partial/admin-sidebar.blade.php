<aside class="dashboard-sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <span>V</span>
    </div>

    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a href="{{ url('/admin') }}" class="sidebar-nav-item admin-switch-item {{ request()->is('admin') ? 'active' : '' }}" title="{{ __('admin.admin_panel') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4.5 10.5v9a1.5 1.5 0 001.5 1.5h12a1.5 1.5 0 001.5-1.5v-9" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.admin_panel') }}</div>
        </a>

        <!-- User Management -->
        <a href="{{ route('admin.user-management') }}" class="sidebar-nav-item admin-switch-item {{ request()->routeIs('admin.user-management') ? 'active' : '' }}" title="{{ __('admin.user_management') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-7.5 0 3.375 3.375 0 017.5 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.user_management') }}</div>
        </a>

        <!-- AI Settings -->
        <a href="{{ route('admin.ai-settings') }}" class="sidebar-nav-item admin-switch-item {{ request()->routeIs('admin.ai-settings') ? 'active' : '' }}" title="{{ __('admin.ai_settings') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423L16.5 15.75l.394 1.183a2.25 2.25 0 001.423 1.423L19.5 18.75l-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.ai_settings') }}</div>
        </a>

        <!-- AI Providers -->
        <a href="{{ route('admin.ai-providers.index') }}" class="sidebar-nav-item admin-switch-item {{ request()->routeIs('admin.ai-providers.*') ? 'active' : '' }}" title="AI Providers">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <div class="sidebar-tooltip">AI Providers</div>
        </a>

        <!-- Agent Management -->
        <a href="{{ route('admin.agents.index') }}" class="sidebar-nav-item admin-switch-item {{ request()->routeIs('admin.agents.*') ? 'active' : '' }}" title="Agent Yönetimi">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <div class="sidebar-tooltip">Agent Yönetimi</div>
        </a>

        <!-- Intent Management -->
        <a href="{{ route('admin.intents.index') }}" class="sidebar-nav-item admin-switch-item {{ request()->routeIs('admin.intents.*') ? 'active' : '' }}" title="Niyet Yönetimi">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <div class="sidebar-tooltip">Niyet Yönetimi</div>
        </a>

        <!-- Analytics -->
        <a href="{{ route('admin.analytics') }}" class="sidebar-nav-item admin-switch-item {{ request()->routeIs('admin.analytics') ? 'active' : '' }}" title="{{ __('admin.analytics') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.analytics') }}</div>
        </a>

        <!-- Admin Settings -->
        <a href="{{ route('admin.settings') }}" class="sidebar-nav-item admin-switch-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}" title="{{ __('admin.system_settings') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.591 1.064c1.513-.947 3.43.97 2.483 2.483a1.724 1.724 0 001.064 2.591c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.064 2.591c.947 1.513-.97 3.43-2.483 2.483a1.724 1.724 0 00-2.591 1.064c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.591-1.064c-1.513.947-3.43-.97-2.483-2.483a1.724 1.724 0 00-1.064-2.591c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.064-2.591c-.947-1.513.97-3.43 2.483-2.483 1.512.947 3.43-.97 2.483-2.483z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.system_settings') }}</div>
        </a>
    </nav>

    <!-- Admin Footer -->
    <div class="sidebar-footer">
        <!-- Switch to User Panel -->
        <div class="sidebar-nav-item admin-switch-item" title="{{ __('admin.user_panel') }}" onclick="window.location.href='{{ route('dashboard') }}'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            <div class="sidebar-tooltip">{{ __('admin.user_panel') }}</div>
        </div>
        
        <!-- Switch to Admin Panel -->
        <div class="sidebar-nav-item admin-switch-item" title="Admin Panel" onclick="window.location.href='{{ route('admin.panel') }}'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            <div class="sidebar-tooltip">Admin Panel</div>
        </div>
    </div>
</aside>

<style>
.admin-switch-item {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

.admin-switch-item:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
    transform: scale(1.05);
}

.admin-switch-item svg {
    color: white;
}

/* Admin sidebar için özel stiller */
.dashboard-sidebar .sidebar-logo span {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: bold;
}
</style> 