@extends('dashboard.partial.admin-layout')

@section('title', 'AI Settings - VersAI Admin')
@section('description', 'AI Settings - VersAI Admin')

@section('content')
    <!-- Page Header -->
    <div class="admin-content">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('admin.ai_settings') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('admin.ai_settings') }}</p>
            </div>
        </div>
    </div>
@endsection 