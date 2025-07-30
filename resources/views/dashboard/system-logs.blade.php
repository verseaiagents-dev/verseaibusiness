@extends('dashboard.partial.admin-layout')

@section('title', 'System Logs - VersAI Admin')
@section('description', 'System Logs - VersAI Admin')

@section('content')
    <!-- Page Header -->
    <div class="admin-content">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('admin.system_logs') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('admin.system_logs') }}</p>
            </div>
        </div>
    </div>
@endsection 