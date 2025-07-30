@extends('dashboard.partial.admin-layout')

@section('title', 'VersAI Admin Panel')
@section('description', 'VersAI Admin Panel')

@section('additional-styles')
<style>
    .modal-overlay {
        backdrop-filter: blur(4px);
    }
    
    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .admin-card {
        transition: all 0.2s ease-in-out;
    }
    
    .admin-card:hover {
        transform: translateY(-2px);
    }
    
    .admin-content {
        margin-bottom: 2rem;
    }
    
    .admin-content:last-child {
        margin-bottom: 0;
    }

    .admin-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .admin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
</style>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="admin-content">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('admin.admin_panel') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('admin.admin_panel_description') }}</p>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Admin Badge -->
                <span class="admin-badge">{{ __('admin.admin_badge') }}</span>
                
                <!-- Switch to User Panel Button -->
                <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    {{ __('admin.user_panel') }}
                </a>
            </div>
        </div>
    </div>
@endsection 