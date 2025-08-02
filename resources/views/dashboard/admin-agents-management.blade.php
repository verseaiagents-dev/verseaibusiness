@extends('dashboard.partial.admin-layout')

@section('title', 'Admin - Agent Yönetimi')
@section('description', 'Proje tablosunda yer alan agent yönetimi')

@section('content')
<div class="admin-content">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Agent Yönetimi</h1>
            <p class="text-gray-600">Proje tablosunda yer alan AI agent yönetimi</p>
        </div>
        <div class="flex space-x-3">
            <button id="addAgentBtn" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Yeni Agent Ekle
            </button>
            <button id="refreshData" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Verileri Yenile
            </button>
        </div>
    </div>

    <!-- Filtreler ve İstatistikler -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Filtreler -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Filtreler</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kullanıcı</label>
                    <select id="userFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tüm Kullanıcılar</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
                    <select id="projectFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tüm Projeler</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }} ({{ $project->user->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sektör</label>
                    <select id="sectorFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tüm Sektörler</option>
                        @foreach($sectors as $key => $sector)
                            <option value="{{ $key }}">{{ $sector }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                    <select id="statusFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tüm Durumlar</option>
                        <option value="1">Aktif</option>
                        <option value="0">Pasif</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- İstatistikler -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">İstatistikler</h3>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-blue-600">Toplam Agent</p>
                            <p class="text-lg font-bold text-blue-900">{{ $totalAgents }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-green-100 text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-green-600">Aktif Agent</p>
                            <p class="text-lg font-bold text-green-900">{{ $activeAgents }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-purple-100 text-purple-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-purple-600">Toplam Proje</p>
                            <p class="text-lg font-bold text-purple-900">{{ $totalProjects }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-yellow-100 text-yellow-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-yellow-600">Knowledge Base</p>
                            <p class="text-lg font-bold text-yellow-900">{{ $totalKnowledgeBase }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-indigo-100 text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-indigo-600">Toplam Niyet</p>
                            <p class="text-lg font-bold text-indigo-900">{{ $totalIntents }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                    <div class="flex items-center">
                        <div class="p-2 rounded-full bg-red-100 text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-red-600">API Events</p>
                            <p class="text-lg font-bold text-red-900">{{ $totalApiEvents }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maliyet Takip Bölümü -->
    <div class="mt-6 bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Maliyet Takibi</h3>
                <p class="text-sm text-gray-600 mt-1">API kullanım maliyetlerini takip edin</p>
            </div>
            <button onclick="updateCostStatistics()" 
                    class="text-gray-500 hover:text-gray-700 transition-colors p-2 rounded-lg hover:bg-gray-100" 
                    title="Maliyet istatistiklerini yenile">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
        </div>
        
        <!-- Maliyet İstatistikleri -->
        <div class="p-6">
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Bugünkü Toplam Maliyet</p>
                            <p class="text-2xl font-bold" id="todayTotalCost">${{ number_format($todayTotalCost, 4) }}</p>
                        </div>
                        <div class="text-blue-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Bu Ay Toplam Maliyet</p>
                            <p class="text-2xl font-bold" id="monthlyTotalCost">${{ number_format($monthlyTotalCost, 4) }}</p>
                        </div>
                        <div class="text-green-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm opacity-90">Toplam Maliyet</p>
                            <p class="text-2xl font-bold" id="totalCost">${{ number_format($totalCost, 4) }}</p>
                        </div>
                        <div class="text-purple-200">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Provider Bazında Maliyetler -->
            @if($providerCosts->count() > 0)
            <div class="mb-6">
                <h4 class="text-md font-medium text-gray-900 mb-3">Provider Bazında Maliyetler</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="providerCostsContainer">
                    @foreach($providerCosts as $provider)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">{{ ucfirst($provider->provider) }}</p>
                                <p class="text-lg font-bold text-gray-900">${{ number_format($provider->total_cost, 4) }}</p>
                                <p class="text-xs text-gray-500">{{ $provider->usage_count }} kullanım</p>
                            </div>
                            <div class="text-gray-400">
                                @if($provider->provider === 'versai')
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                    </svg>
                                @elseif($provider->provider === 'openai')
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Agent Listesi -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Agent Listesi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kullanıcı</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proje</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sektör</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Niyet Sayısı</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Bugünkü Maliyet</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aylık Maliyet</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Toplam Maliyet</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="agentsTableBody">
                    @foreach($agents as $agent)
                    <tr class="agent-row hover:bg-gray-50" 
                        data-user="{{ $agent->user->name }}" 
                        data-project="{{ $agent->project->name ?? 'N/A' }}" 
                        data-sector="{{ $agent->sector }}" 
                        data-status="{{ $agent->is_active ? 'Aktif' : 'Pasif' }}"
                        data-agent-id="{{ $agent->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-blue-600">{{ substr($agent->name, 0, 2) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $agent->name }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($agent->description, 50) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $agent->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $agent->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $agent->project->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $sectors[$agent->sector] ?? $agent->sector }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm text-gray-900">{{ $agent->intents->count() }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm font-medium text-green-600 today-cost">${{ number_format($agentCosts[$agent->id]['today_cost'] ?? 0, 4) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm font-medium text-blue-600 monthly-cost">${{ number_format($agentCosts[$agent->id]['monthly_cost'] ?? 0, 4) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="text-sm font-medium text-purple-600 total-cost">${{ number_format($agentCosts[$agent->id]['total_cost'] ?? 0, 4) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($agent->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Pasif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex items-center justify-center space-x-2">
                                <button onclick="viewAgent({{ $agent->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors" 
                                        title="Görüntüle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button onclick="editAgent({{ $agent->id }})" 
                                        class="text-indigo-600 hover:text-indigo-900 transition-colors" 
                                        title="Düzenle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button onclick="toggleAgentStatus({{ $agent->id }})" 
                                        class="text-yellow-600 hover:text-yellow-900 transition-colors" 
                                        title="{{ $agent->is_active ? 'Pasif Yap' : 'Aktif Yap' }}">
                                    @if($agent->is_active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    @endif
                                </button>
                                <button onclick="deleteAgent({{ $agent->id }})" 
                                        class="text-red-600 hover:text-red-900 transition-colors" 
                                        title="Sil">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Agent Ekleme/Düzenleme Modal -->
<div id="agentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Yeni Agent Ekle</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <form id="agentForm">
                <!-- Hidden user_id field for POST operations -->
                <input type="hidden" name="user_id" id="hidden_user_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Proje</label>
                    <select name="project_id" id="project_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Proje Seçin</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" data-user-id="{{ $project->user_id }}">{{ $project->name }} ({{ $project->user->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Agent Adı</label>
                    <input type="text" name="name" id="agent_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sektör</label>
                    <select name="sector" id="agent_sector" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Sektör Seçin</option>
                        @foreach($sectors as $key => $sector)
                            <option value="{{ $key }}">{{ $sector }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                    <textarea name="description" id="agent_description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Agent açıklaması..."></textarea>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                        İptal
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Filtreleme fonksiyonları
document.getElementById('userFilter').addEventListener('change', filterAgents);
document.getElementById('projectFilter').addEventListener('change', filterAgents);
document.getElementById('sectorFilter').addEventListener('change', filterAgents);
document.getElementById('statusFilter').addEventListener('change', filterAgents);

function filterAgents() {
    const userFilter = document.getElementById('userFilter').value;
    const projectFilter = document.getElementById('projectFilter').value;
    const sectorFilter = document.getElementById('sectorFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const rows = document.querySelectorAll('.agent-row');
    
    rows.forEach(row => {
        const user = row.dataset.user;
        const project = row.dataset.project;
        const sector = row.dataset.sector;
        const status = row.dataset.status;
        
        const userMatch = !userFilter || user === userFilter;
        const projectMatch = !projectFilter || project === projectFilter;
        const sectorMatch = !sectorFilter || sector === sectorFilter;
        const statusMatch = !statusFilter || status === statusFilter;
        
        if (userMatch && projectMatch && sectorMatch && statusMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function viewAgent(agentId) {
    window.open(`/admin/agents/${agentId}`, '_blank');
}

function editAgent(agentId) {
    // Agent verilerini getir ve modal'ı doldur
    fetch(`/admin/agents/${agentId}/edit-data`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const agent = data.agent;
                
                // Modal başlığını güncelle
                document.getElementById('modalTitle').textContent = 'Agent Düzenle';
                
                // Form verilerini doldur
                document.getElementById('agent_name').value = agent.name;
                document.getElementById('agent_sector').value = agent.sector;
                document.getElementById('agent_description').value = agent.description || '';
                document.getElementById('project_id').value = agent.project_id;
                
                // Hidden user_id'yi set et
                document.getElementById('hidden_user_id').value = agent.user_id;
                
                // Form'a agent ID'sini ekle
                document.getElementById('agentForm').dataset.agentId = agentId;
                
                // Modal'ı aç
                document.getElementById('agentModal').classList.remove('hidden');
            } else {
                alert('Agent verileri yüklenirken hata oluştu');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Agent verileri yüklenirken hata oluştu');
        });
}

function toggleAgentStatus(agentId) {
    if (confirm('Bu agent\'ın durumunu değiştirmek istediğinizden emin misiniz?')) {
        fetch(`/admin/agents/${agentId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu');
        });
    }
}

function deleteAgent(agentId) {
    if (confirm('Bu agent\'ı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
        fetch(`/admin/agents/${agentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu');
        });
    }
}

function closeModal() {
    document.getElementById('agentModal').classList.add('hidden');
    document.getElementById('agentForm').reset();
    document.getElementById('agentForm').removeAttribute('data-agent-id');
    document.getElementById('hidden_user_id').value = '';
}

// Proje seçildiğinde user_id'yi otomatik set et
document.getElementById('project_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const userId = selectedOption.getAttribute('data-user-id');
    document.getElementById('hidden_user_id').value = userId || '';
});

// Gerçek zamanlı maliyet takibi
function updateCostStatistics() {
    fetch('/admin/cost-statistics')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Ana maliyet istatistiklerini güncelle
                document.getElementById('todayTotalCost').textContent = '$' + parseFloat(data.todayTotalCost).toFixed(4);
                document.getElementById('monthlyTotalCost').textContent = '$' + parseFloat(data.monthlyTotalCost).toFixed(4);
                document.getElementById('totalCost').textContent = '$' + parseFloat(data.totalCost).toFixed(4);
                
                // Provider bazında maliyetleri güncelle
                if (data.providerCosts && data.providerCosts.length > 0) {
                    const container = document.getElementById('providerCostsContainer');
                    if (container) {
                        container.innerHTML = '';
                        data.providerCosts.forEach(provider => {
                            const providerHtml = `
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">${provider.provider.charAt(0).toUpperCase() + provider.provider.slice(1)}</p>
                                            <p class="text-lg font-bold text-gray-900">$${parseFloat(provider.total_cost).toFixed(4)}</p>
                                            <p class="text-xs text-gray-500">${provider.usage_count} kullanım</p>
                                        </div>
                                        <div class="text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.innerHTML += providerHtml;
                        });
                    }
                }
                
                // Agent tablosundaki maliyetleri güncelle
                if (data.agentCosts) {
                    Object.keys(data.agentCosts).forEach(agentId => {
                        const agentCost = data.agentCosts[agentId];
                        const row = document.querySelector(`tr[data-agent-id="${agentId}"]`);
                        if (row) {
                            const todayCostCell = row.querySelector('.today-cost');
                            const monthlyCostCell = row.querySelector('.monthly-cost');
                            const totalCostCell = row.querySelector('.total-cost');
                            
                            if (todayCostCell) todayCostCell.textContent = '$' + parseFloat(agentCost.today_cost).toFixed(4);
                            if (monthlyCostCell) monthlyCostCell.textContent = '$' + parseFloat(agentCost.monthly_cost).toFixed(4);
                            if (totalCostCell) totalCostCell.textContent = '$' + parseFloat(agentCost.total_cost).toFixed(4);
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Maliyet istatistikleri güncellenirken hata:', error);
        });
}

// Her 30 saniyede bir maliyet istatistiklerini güncelle
setInterval(updateCostStatistics, 30000);

// Sayfa yüklendiğinde ilk güncellemeyi yap
document.addEventListener('DOMContentLoaded', function() {
    // İlk güncelleme
    updateCostStatistics();
});

// Agent ekleme/düzenleme formu
document.getElementById('addAgentBtn').addEventListener('click', function() {
    document.getElementById('modalTitle').textContent = 'Yeni Agent Ekle';
    document.getElementById('agentForm').removeAttribute('data-agent-id');
    document.getElementById('agentModal').classList.remove('hidden');
});

document.getElementById('agentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const agentId = this.dataset.agentId;
    const url = agentId ? `/admin/agents/${agentId}` : '/admin/agents';
    const method = agentId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            closeModal();
            location.reload();
        } else {
            alert('Bir hata oluştu: ' + (data.error || 'Bilinmeyen hata'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
});
</script>
@endsection 