@extends('dashboard.partial.admin-layout')

@section('title', 'Admin - Niyet Yönetimi')
@section('description', 'Tüm kullanıcıların niyet yönetimi ve otomatik öneriler')

@section('content')
<div class="admin-content">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Niyet Yönetimi</h1>
            <p class="text-gray-600">Tüm kullanıcıların niyet yönetimi ve otomatik öneriler</p>
        </div>
        <div class="flex space-x-3">
            <button id="addIntentTemplateBtn" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Şablon Ekle
            </button>
            <button id="refreshData" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Yenile
            </button>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 lg:p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base lg:text-lg font-semibold">Otomatik Öneriler</h3>
                    <p class="text-blue-100 text-xs lg:text-sm">Yeni kullanıcılar için</p>
                </div>
                <div class="text-2xl lg:text-3xl font-bold">{{ $intents->where('is_active', true)->count() }}</div>
            </div>
            <button onclick="showAutoSuggestions()" class="mt-3 lg:mt-4 bg-white bg-opacity-20 hover:bg-opacity-30 text-blue-900 font-medium px-3 lg:px-4 py-1.5 lg:py-2 rounded-lg transition-all text-xs lg:text-sm">
                Önerileri Görüntüle
            </button>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 lg:p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base lg:text-lg font-semibold">Aktif Niyetler</h3>
                    <p class="text-green-100 text-xs lg:text-sm">Sistemde aktif</p>
                </div>
                <div class="text-2xl lg:text-3xl font-bold">{{ $intents->where('is_active', true)->count() }}</div>
            </div>
            <button onclick="showActiveIntents()" class="mt-3 lg:mt-4 bg-white bg-opacity-20 hover:bg-opacity-30 text-green-900 font-medium px-3 lg:px-4 py-1.5 lg:py-2 rounded-lg transition-all text-xs lg:text-sm">
                Detayları Gör
            </button>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 lg:p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base lg:text-lg font-semibold">Toplam Kullanıcı</h3>
                    <p class="text-purple-100 text-xs lg:text-sm">Sisteme kayıtlı</p>
                </div>
                <div class="text-2xl lg:text-3xl font-bold">{{ $agents->unique('user_id')->count() }}</div>
            </div>
            <button onclick="showUserStats()" class="mt-3 lg:mt-4 bg-white bg-opacity-20 hover:bg-opacity-30 text-purple-900 font-medium px-3 lg:px-4 py-1.5 lg:py-2 rounded-lg transition-all text-xs lg:text-sm">
                İstatistikleri Gör
            </button>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Gelişmiş Filtreler</h3>
            <button id="toggleFilters" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                </svg>
                Filtreleri Göster/Gizle
            </button>
        </div>
        
        <div id="filterSection" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kullanıcı</label>
                <select id="userFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tüm Kullanıcılar</option>
                    @foreach($agents->unique('user_id') as $agent)
                        <option value="{{ $agent->user->id }}">{{ $agent->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sektör</label>
                <select id="sectorFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tüm Sektörler</option>
                    <option value="ecommerce">E-Ticaret</option>
                    <option value="real_estate">Emlak</option>
                    <option value="hotel">Otel</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                <select id="statusFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tüm Durumlar</option>
                    <option value="1">Aktif</option>
                    <option value="0">Pasif</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Arama</label>
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Niyet adı ara..." class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Intent Templates Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">Otomatik Intent Şablonları</h3>
            <button id="addTemplateBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Yeni Şablon
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="templatesGrid">
            <!-- E-Ticaret Şablonları -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold text-blue-900">E-Ticaret</h4>
                    <span class="px-2 py-1 bg-blue-200 text-blue-800 text-xs rounded-full">4 Şablon</span>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-blue-700">Ürün Arama</span>
                        <button class="text-blue-600 hover:text-blue-800 text-xs">Ekle</button>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-blue-700">Sepet İşlemleri</span>
                        <button class="text-blue-600 hover:text-blue-800 text-xs">Ekle</button>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-blue-700">Sipariş Takibi</span>
                        <button class="text-blue-600 hover:text-blue-800 text-xs">Ekle</button>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-blue-700">Stok Sorgulama</span>
                        <button class="text-blue-600 hover:text-blue-800 text-xs">Ekle</button>
                    </div>
                </div>
            </div>
            
            <!-- Emlak Şablonları -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold text-green-900">Emlak</h4>
                    <span class="px-2 py-1 bg-green-200 text-green-800 text-xs rounded-full">3 Şablon</span>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-green-700">Emlak Arama</span>
                        <button class="text-green-600 hover:text-green-800 text-xs">Ekle</button>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-green-700">Randevu Talebi</span>
                        <button class="text-green-600 hover:text-green-800 text-xs">Ekle</button>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-green-700">Fiyat Hesaplama</span>
                        <button class="text-green-600 hover:text-green-800 text-xs">Ekle</button>
                    </div>
                </div>
            </div>
            
            <!-- Otel Şablonları -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-semibold text-purple-900">Otel</h4>
                    <span class="px-2 py-1 bg-purple-200 text-purple-800 text-xs rounded-full">3 Şablon</span>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-purple-700">Oda Rezervasyonu</span>
                        <button class="text-purple-600 hover:text-purple-800 text-xs">Ekle</button>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-purple-700">Fiyat Sorgulama</span>
                        <button class="text-purple-600 hover:text-purple-800 text-xs">Ekle</button>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-purple-700">Rezervasyon İptali</span>
                        <button class="text-purple-600 hover:text-purple-800 text-xs">Ekle</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Intent List -->
    <div class="bg-white rounded-xl shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Niyet Listesi</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500" id="intentCount">{{ $intents->count() }} niyet bulundu</span>
                    <div class="flex items-center space-x-1">
                        <button id="viewModeGrid" class="p-2 text-blue-600 bg-blue-50 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                        </button>
                        <button id="viewModeList" class="p-2 text-gray-400 hover:text-blue-600 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Grid View -->
        <div id="gridView" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="intentsGrid">
                @foreach($intents as $intent)
                <div class="intent-card bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all duration-200" data-user="{{ $intent->agent->user->id }}" data-sector="{{ $intent->agent->sector }}" data-status="{{ $intent->is_active ? '1' : '0' }}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 mb-1">{{ $intent->name }}</h4>
                            <p class="text-sm text-gray-600">{{ Str::limit($intent->description, 60) }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs rounded-full {{ $intent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $intent->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ $intent->agent->user->name }}
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            {{ $intent->agent->name }}
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            {{ $intent->agent->sector == 'ecommerce' ? 'E-Ticaret' : ($intent->agent->sector == 'real_estate' ? 'Emlak' : ($intent->agent->sector == 'hotel' ? 'Otel' : ucfirst($intent->agent->sector))) }}
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <span class="text-xs text-gray-500">{{ $intent->created_at->format('d.m.Y H:i') }}</span>
                        <div class="flex items-center space-x-1">
                            <button onclick="viewIntent({{ $intent->id }})" class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button onclick="toggleIntentStatus({{ $intent->id }})" class="p-1 text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                            <button onclick="deleteIntent({{ $intent->id }})" class="p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- List View (Hidden by default) -->
        <div id="listView" class="hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Niyet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kullanıcı</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sektör</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oluşturulma</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="intentsTableBody">
                        @foreach($intents as $intent)
                        <tr class="intent-row" data-user="{{ $intent->agent->user->id }}" data-sector="{{ $intent->agent->sector }}" data-status="{{ $intent->is_active ? '1' : '0' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $intent->name }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($intent->description, 50) }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $intent->agent->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $intent->agent->user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $intent->agent->name }}</div>
                                <div class="text-sm text-gray-500">{{ $intent->agent->sector_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $intent->agent->sector == 'ecommerce' ? 'bg-blue-100 text-blue-800' : 
                                       ($intent->agent->sector == 'real_estate' ? 'bg-green-100 text-green-800' : 
                                       ($intent->agent->sector == 'hotel' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ $intent->agent->sector == 'ecommerce' ? 'E-Ticaret' : 
                                       ($intent->agent->sector == 'real_estate' ? 'Emlak' : 
                                       ($intent->agent->sector == 'hotel' ? 'Otel' : ucfirst($intent->agent->sector))) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $intent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $intent->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $intent->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="viewIntent({{ $intent->id }})" class="text-blue-600 hover:text-blue-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="toggleIntentStatus({{ $intent->id }})" class="text-yellow-600 hover:text-yellow-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteIntent({{ $intent->id }})" class="text-red-600 hover:text-red-900">
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
</div>

<!-- Template Modal -->
<div id="templateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Yeni Intent Şablonu Ekle</h3>
            <button onclick="closeTemplateModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <form id="templateForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Şablon Adı</label>
                    <input type="text" name="template_name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sektör</label>
                    <select name="sector" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Sektör Seçin</option>
                        <option value="ecommerce">E-Ticaret</option>
                        <option value="real_estate">Emlak</option>
                        <option value="hotel">Otel</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                    <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Şablon açıklaması..."></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Eğitim Verileri</label>
                    <textarea name="training_data" rows="4" placeholder="Örnek kullanıcı mesajları (her satıra bir mesaj)..." class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Her satıra bir örnek mesaj yazın</p>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeTemplateModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
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
document.getElementById('userFilter').addEventListener('change', filterIntents);
document.getElementById('sectorFilter').addEventListener('change', filterIntents);
document.getElementById('statusFilter').addEventListener('change', filterIntents);
document.getElementById('searchInput').addEventListener('input', filterIntents);

// View mode toggle
document.getElementById('viewModeGrid').addEventListener('click', function() {
    document.getElementById('gridView').classList.remove('hidden');
    document.getElementById('listView').classList.add('hidden');
    this.classList.add('text-blue-600', 'bg-blue-50');
    this.classList.remove('text-gray-400');
    document.getElementById('viewModeList').classList.remove('text-blue-600', 'bg-blue-50');
    document.getElementById('viewModeList').classList.add('text-gray-400');
});

document.getElementById('viewModeList').addEventListener('click', function() {
    document.getElementById('listView').classList.remove('hidden');
    document.getElementById('gridView').classList.add('hidden');
    this.classList.add('text-blue-600', 'bg-blue-50');
    this.classList.remove('text-gray-400');
    document.getElementById('viewModeGrid').classList.remove('text-blue-600', 'bg-blue-50');
    document.getElementById('viewModeGrid').classList.add('text-gray-400');
});

// Filter toggle
document.getElementById('toggleFilters').addEventListener('click', function() {
    const filterSection = document.getElementById('filterSection');
    filterSection.classList.toggle('hidden');
});

function filterIntents() {
    const userFilter = document.getElementById('userFilter').value;
    const sectorFilter = document.getElementById('sectorFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    
    const cards = document.querySelectorAll('.intent-card');
    const rows = document.querySelectorAll('.intent-row');
    
    let visibleCount = 0;
    
    // Grid view filtering
    cards.forEach(card => {
        const user = card.dataset.user;
        const sector = card.dataset.sector;
        const status = card.dataset.status;
        const intentName = card.querySelector('h4').textContent.toLowerCase();
        
        const userMatch = !userFilter || user === userFilter;
        const sectorMatch = !sectorFilter || sector === sectorFilter;
        const statusMatch = !statusFilter || status === statusFilter;
        const searchMatch = !searchInput || intentName.includes(searchInput);
        
        if (userMatch && sectorMatch && statusMatch && searchMatch) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // List view filtering
    rows.forEach(row => {
        const user = row.dataset.user;
        const sector = row.dataset.sector;
        const status = row.dataset.status;
        const intentName = row.querySelector('td:first-child .text-sm.font-medium').textContent.toLowerCase();
        
        const userMatch = !userFilter || user === userFilter;
        const sectorMatch = !sectorFilter || sector === sectorFilter;
        const statusMatch = !statusFilter || status === statusFilter;
        const searchMatch = !searchInput || intentName.includes(searchInput);
        
        if (userMatch && sectorMatch && statusMatch && searchMatch) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('intentCount').textContent = `${visibleCount} niyet bulundu`;
}

function showAutoSuggestions() {
    // Otomatik öneriler modal'ı açılacak
    alert('Otomatik öneriler özelliği yakında eklenecek!');
}

function showActiveIntents() {
    document.getElementById('statusFilter').value = '1';
    filterIntents();
}

function showUserStats() {
    // Kullanıcı istatistikleri modal'ı açılacak
    alert('Kullanıcı istatistikleri özelliği yakında eklenecek!');
}

function viewIntent(intentId) {
    window.open(`/admin/intents/${intentId}`, '_blank');
}

function toggleIntentStatus(intentId) {
    if (confirm('Bu niyetin durumunu değiştirmek istediğinizden emin misiniz?')) {
        fetch(`/admin/intents/${intentId}/toggle-status`, {
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

function deleteIntent(intentId) {
    if (confirm('Bu niyeti silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
        fetch(`/admin/intents/${intentId}`, {
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

// Template modal functions
document.getElementById('addTemplateBtn').addEventListener('click', function() {
    document.getElementById('templateModal').classList.remove('hidden');
    // Body scroll'u engelle
    document.body.style.overflow = 'hidden';
});

function closeTemplateModal() {
    document.getElementById('templateModal').classList.add('hidden');
    document.getElementById('templateForm').reset();
    // Body scroll'u geri aç
    document.body.style.overflow = '';
}

// Modal dışına tıklandığında kapatma
document.getElementById('templateModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeTemplateModal();
    }
});

// ESC tuşu ile kapatma
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('templateModal').classList.contains('hidden')) {
        closeTemplateModal();
    }
});

document.getElementById('templateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Submit butonunu devre dışı bırak
    submitBtn.disabled = true;
    submitBtn.textContent = 'Kaydediliyor...';
    
    fetch('/admin/intent-templates', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            closeTemplateModal();
            // Başarı mesajı göster
            showNotification('Şablon başarıyla eklendi!', 'success');
            // Sayfayı yenile
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Bir hata oluştu', 'error');
    })
    .finally(() => {
        // Submit butonunu geri aç
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Bildirim gösterme fonksiyonu
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // 3 saniye sonra kaldır
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', function() {
    filterIntents();
});
</script>
@endsection 