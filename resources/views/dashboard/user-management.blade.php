@extends('dashboard.partial.admin-layout')

@section('title', 'User Management - VersAI Admin')
@section('description', 'User Management - VersAI Admin')

@section('content')
    <!-- Page Header -->
    <div class="admin-content">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('admin.user_management') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('admin.user_management') }}</p>
            </div>
        </div>
    </div>
@endsection 