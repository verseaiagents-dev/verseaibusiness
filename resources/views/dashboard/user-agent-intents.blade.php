@extends('dashboard.partial.user-layout')

@section('title', $agent->name . ' - Niyet Yönetimi')
@section('description', 'AI Agent Niyet ve Eylem Yönetimi')

@section('content')
<div class="admin-content">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-2">
                <a href="{{ route('user.intents.index') }}" class="text-blue-500 hover:text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ $agent->name }}</h1>
                <span class="px-3 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                    {{ $agent->sector == 'ecommerce' ? 'E-Ticaret' : ($agent->sector == 'real_estate' ? 'Emlak' : ($agent->sector == 'hotel' ? 'Otel' : ucfirst($agent->sector))) }}
                </span>
            </div>
            <p class="text-gray-600">{{ $agent->sector_name }} - Niyet Yönetimi</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('user.api-events.show', $agent) }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                API Event Yönet
            </a>
            <button id="addIntentBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                Yeni Niyet Ekle
            </button>
        </div>
    </div>

    <!-- İstatistikler -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @php
            $totalIntents = $intents->count();
            $activeIntents = $intents->where('is_active', true)->count();
            $systemIntents = $intents->where('config.is_system', true)->count();
            $customIntents = $intents->where('config.is_system', false)->count();
        @endphp
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Toplam Niyet</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalIntents }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Aktif Niyet</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $activeIntents }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Sistem Niyet</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $systemIntents }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Özel Niyet</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $customIntents }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sektör Bazlı Niyet Şablonları -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Sektör Niyet Şablonları</h2>
                    <p class="text-sm text-gray-600 mt-1">Tüm sektörler için önerilen niyet şablonları</p>
                </div>
                <div class="flex space-x-2">
                    <!-- Butonlar kaldırıldı -->
                </div>
            </div>
        </div>
        
        <!-- Sektör Filtreleme -->
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <div class="flex flex-wrap gap-2">
                <button class="sector-filter px-3 py-1 rounded-full text-sm transition-colors active-sector" data-sector="all">
                    Tüm Sektörler
                </button>
                <button class="sector-filter px-3 py-1 rounded-full text-sm transition-colors" data-sector="ecommerce">
                    E-Ticaret
                </button>
                <button class="sector-filter px-3 py-1 rounded-full text-sm transition-colors" data-sector="real_estate">
                    Emlak
                </button>
                <button class="sector-filter px-3 py-1 rounded-full text-sm transition-colors" data-sector="hotel">
                    Otel
                </button>
                <button class="sector-filter px-3 py-1 rounded-full text-sm transition-colors" data-sector="general">
                    Genel
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Sektör Grupları -->
            <div id="sectorTemplatesContainer">
                <!-- E-Ticaret Sektörü -->
                <div class="sector-group mb-8 cursor-pointer hover:bg-gray-50 transition-colors duration-200" data-sector="ecommerce" onclick="showEcommerceModal()">
                    <div class="flex items-center justify-between mb-4 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">E-Ticaret Sektörü</h3>
                                <p class="text-sm text-gray-600">E-ticaret işlemleri için özel şablonlar</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">3 Şablon</span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="ecommerceTemplates">
                        <!-- E-ticaret şablonları buraya yüklenecek -->
                    </div>
                </div>

                <!-- Emlak Sektörü -->
                <div class="sector-group mb-8" data-sector="real_estate">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Emlak Sektörü</h3>
                                <p class="text-sm text-gray-600">Emlak işlemleri için özel şablonlar</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs bg-green-100 text-green-800 rounded-full">3 Şablon</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="realEstateTemplates">
                        <!-- Emlak şablonları buraya yüklenecek -->
                    </div>
                </div>

                <!-- Otel Sektörü -->
                <div class="sector-group mb-8" data-sector="hotel">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v4m0 0v4m0-4h4m-4 0H8"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Otel Sektörü</h3>
                                <p class="text-sm text-gray-600">Otel işlemleri için özel şablonlar</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs bg-purple-100 text-purple-800 rounded-full">3 Şablon</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="hotelTemplates">
                        <!-- Otel şablonları buraya yüklenecek -->
                    </div>
                </div>

                <!-- Genel Sektör -->
                <div class="sector-group mb-8" data-sector="general">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Genel Sektör</h3>
                                <p class="text-sm text-gray-600">Genel kullanım için şablonlar</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">2 Şablon</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="generalTemplates">
                        <!-- Genel şablonlar buraya yüklenecek -->
                    </div>
                </div>
            </div>

            <!-- Yükleme Durumu -->
            <div id="loadingState" class="text-center py-8 hidden">
                <div class="inline-flex items-center space-x-2">
                    <svg class="animate-spin h-5 w-5 text-blue-600 loading-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-gray-600">Şablonlar yükleniyor...</span>
                </div>
            </div>

            <!-- Boş Durum -->
            <div id="emptyState" class="text-center py-8">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-600">Şablonları yüklemek için butona tıklayın</p>
            </div>
        </div>
    </div>

    <!-- Niyetler Listesi -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Mevcut Niyetler</h2>
        </div>
        
        <div class="p-6">
            <div class="space-y-4">
                @foreach($intents as $intent)
                <div class="border border-gray-200 rounded-lg p-4" data-intent-id="{{ $intent->id }}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ ucfirst(str_replace('_', ' ', $intent->name)) }}
                                </h3>
                                @if($intent->config['is_system'] ?? false)
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Sistem</span>
                                @else
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Özel</span>
                                @endif
                            </div>
                            
                            @if($intent->description)
                            <p class="text-sm text-gray-600 mb-3">{{ $intent->description }}</p>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="intent-toggle rounded text-blue-600" 
                                       data-intent-id="{{ $intent->id }}"
                                       {{ $intent->is_active ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Aktif</span>
                            </label>
                            
                            @if(!($intent->config['is_system'] ?? false))
                            <button class="delete-intent text-red-500 hover:text-red-700" 
                                    data-intent-id="{{ $intent->id }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Niyet Detayları -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Anahtar Kelimeler -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Anahtar Kelimeler</h4>
                            <div class="flex flex-wrap gap-1">
                                @if(isset($intent->config['keywords']))
                                    @foreach($intent->config['keywords'] as $keyword)
                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">{{ $keyword }}</span>
                                    @endforeach
                                @else
                                    <span class="text-xs text-gray-500">Tanımlanmamış</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Aksiyonlar -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Aksiyonlar</h4>
                            <div class="flex flex-wrap gap-1">
                                @if(isset($intent->config['actions']))
                                    @foreach($intent->config['actions'] as $action)
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded">{{ $action }}</span>
                                    @endforeach
                                @else
                                    <span class="text-xs text-gray-500">Tanımlanmamış</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Yanıt Tipi -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Yanıt Tipi</h4>
                            <span class="text-xs px-2 py-1 bg-purple-100 text-purple-800 rounded">
                                {{ $intent->config['response_type'] ?? 'Tanımlanmamış' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
                
                @if($intents->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz Niyet Tanımlanmamış</h3>
                    <p class="text-gray-600">Bu agent için niyet tanımlayarak başlayın.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- E-Ticaret Şablonları Modal -->
<div id="ecommerceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-3/4 max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">E-Ticaret Şablonları</h2>
                    <p class="text-sm text-gray-600">E-ticaret işlemleri için özel şablonlar</p>
                </div>
            </div>
            <button id="closeEcommerceModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="ecommerceModalTemplates">
            <!-- E-ticaret şablonları buraya yüklenecek -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Ürün Arama</h3>
                        <p class="text-sm text-gray-600 mb-3">Kullanıcıların ürün araması yapmasını sağlar</p>
                    </div>
                    <button class="create-from-template bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm transition-colors"
                            data-template-key="product_search">
                        Oluştur
                    </button>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">HTTP Metodu:</span>
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded">GET</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">API Endpoint:</span>
                        <span class="text-gray-500">/api/products/search</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">Anahtar Kelimeler:</span>
                        <span class="text-gray-500">ürün, ara, bul, arama</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">Aksiyonlar:</span>
                        <span class="text-gray-500">search_products, filter_results</span>
                    </div>
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Sepete Ekleme</h3>
                        <p class="text-sm text-gray-600 mb-3">Ürünleri sepete ekleme işlemi</p>
                    </div>
                    <button class="create-from-template bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm transition-colors"
                            data-template-key="add_to_cart">
                        Oluştur
                    </button>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">HTTP Metodu:</span>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">POST</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">API Endpoint:</span>
                        <span class="text-gray-500">/api/cart/add</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">Anahtar Kelimeler:</span>
                        <span class="text-gray-500">sepete ekle, ekle, satın al</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">Aksiyonlar:</span>
                        <span class="text-gray-500">add_to_cart, update_quantity</span>
                    </div>
                </div>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Sipariş Durumu</h3>
                        <p class="text-sm text-gray-600 mb-3">Sipariş durumu sorgulama</p>
                    </div>
                    <button class="create-from-template bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm transition-colors"
                            data-template-key="order_status">
                        Oluştur
                    </button>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">HTTP Metodu:</span>
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded">GET</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">API Endpoint:</span>
                        <span class="text-gray-500">/api/orders/status</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">Anahtar Kelimeler:</span>
                        <span class="text-gray-500">sipariş, durum, takip</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">Aksiyonlar:</span>
                        <span class="text-gray-500">check_order_status, track_order</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Niyet Ekleme Modal -->
<div id="intentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-1/2 max-w-2xl mx-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Yeni Niyet Ekle</h2>
            <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="intentForm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Niyet Adı *</label>
                    <input type="text" name="name" required 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Örn: ürün_sepete_ekle">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Yanıt Tipi *</label>
                    <select name="config[response_type]" required 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seçin</option>
                        @if($agent->sector == 'ecommerce')
                            <option value="product_list">Ürün Listesi</option>
                            <option value="cart_action">Sepet İşlemi</option>
                            <option value="order_status">Sipariş Durumu</option>
                            <option value="stock_info">Stok Bilgisi</option>
                        @elseif($agent->sector == 'real_estate')
                            <option value="property_search">Emlak Arama</option>
                            <option value="appointment_request">Randevu Talebi</option>
                            <option value="price_calculation">Fiyat Hesaplama</option>
                            <option value="availability_check">Müsaitlik Kontrolü</option>
                        @elseif($agent->sector == 'hotel')
                            <option value="room_booking">Oda Rezervasyonu</option>
                            <option value="availability_check">Müsaitlik Kontrolü</option>
                            <option value="price_inquiry">Fiyat Sorgulama</option>
                            <option value="booking_cancellation">Rezervasyon İptali</option>
                        @else
                            <option value="general_info">Genel Bilgi</option>
                            <option value="custom_action">Özel Aksiyon</option>
                        @endif
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
                <textarea name="description" rows="3" 
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Bu niyetin ne yaptığını açıklayın..."></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Anahtar Kelimeler *</label>
                <div id="keywordsContainer" class="space-y-2">
                    <div class="flex space-x-2">
                        <input type="text" name="config[keywords][]" required 
                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Anahtar kelime">
                        <button type="button" id="addKeyword" class="px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                            +
                        </button>
                    </div>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Aksiyonlar *</label>
                <div id="actionsContainer" class="space-y-2">
                    <div class="flex space-x-2">
                        <input type="text" name="config[actions][]" required 
                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Aksiyon adı">
                        <button type="button" id="addAction" class="px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                            +
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" id="cancelBtn" class="px-4 py-2 text-gray-600 hover:text-gray-800">İptal</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Niyet Oluştur</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal kontrolü
    const modal = document.getElementById('intentModal');
    const ecommerceModal = document.getElementById('ecommerceModal');
    const addBtn = document.getElementById('addIntentBtn');
    const closeBtn = document.getElementById('closeModal');
    const closeEcommerceModal = document.getElementById('closeEcommerceModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const form = document.getElementById('intentForm');
    const sectorTemplatesContainer = document.getElementById('sectorTemplatesContainer');
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');

    addBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    closeEcommerceModal.addEventListener('click', () => ecommerceModal.classList.add('hidden'));
    cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));

    // Modal dışına tıklama ile kapatma
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
    
    ecommerceModal.addEventListener('click', (e) => {
        if (e.target === ecommerceModal) {
            ecommerceModal.classList.add('hidden');
        }
    });

    // E-ticaret modal fonksiyonu
    window.showEcommerceModal = function() {
        ecommerceModal.classList.remove('hidden');
    };

    // Sektör filtreleme butonları
    document.querySelectorAll('.sector-filter').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.sector-filter').forEach(btn => btn.classList.remove('active-sector'));
            this.classList.add('active-sector');
            const sector = this.dataset.sector;
            filterSectors(sector);
        });
    });

    // Sektör filtreleme fonksiyonu
    function filterSectors(selectedSector) {
        const sectorGroups = document.querySelectorAll('.sector-group');
        sectorGroups.forEach(group => {
            if (selectedSector === 'all' || group.dataset.sector === selectedSector) {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
            }
        });
    }

    // E-ticaret modal şablon butonları
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('create-from-template')) {
            const templateKey = e.target.dataset.templateKey;
            console.log('Şablon oluşturma butonu tıklandı:', templateKey);
            
            // Şablon oluşturma işlemi burada yapılacak
            showNotification(`${templateKey} şablonu oluşturuluyor...`, 'success');
        }
    });



    // Anahtar kelime ekleme
    document.getElementById('addKeyword').addEventListener('click', function() {
        const container = document.getElementById('keywordsContainer');
        const newField = document.createElement('div');
        newField.className = 'flex space-x-2';
        newField.innerHTML = `
            <input type="text" name="config[keywords][]" 
                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="Anahtar kelime">
            <button type="button" class="remove-field px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200">-</button>
        `;
        container.appendChild(newField);
    });

    // Aksiyon ekleme
    document.getElementById('addAction').addEventListener('click', function() {
        const container = document.getElementById('actionsContainer');
        const newField = document.createElement('div');
        newField.className = 'flex space-x-2';
        newField.innerHTML = `
            <input type="text" name="config[actions][]" 
                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                   placeholder="Aksiyon adı">
            <button type="button" class="remove-field px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200">-</button>
        `;
        container.appendChild(newField);
    });

    // Alan silme
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-field')) {
            e.target.parentElement.remove();
        }
    });

    // Form gönderimi
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            const response = await fetch(`/user/intents/{{ $agent->id }}/intents`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                showNotification(result.message, 'success');
                modal.classList.add('hidden');
                setTimeout(() => location.reload(), 1000);
            } else {
                const error = await response.json();
                showNotification(error.message || 'Bir hata oluştu', 'error');
            }
        } catch (error) {
            showNotification('Bir hata oluştu', 'error');
        }
    });

    // Niyet toggle
    document.querySelectorAll('.intent-toggle').forEach(toggle => {
        toggle.addEventListener('change', async function() {
            const intentId = this.dataset.intentId;
            const isActive = this.checked;
            
            try {
                const response = await fetch(`/api/intents/${intentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ is_active: isActive })
                });
                
                if (response.ok) {
                    const result = await response.json();
                    showNotification(result.message, 'success');
                }
            } catch (error) {
                showNotification('Güncellenirken hata oluştu', 'error');
                this.checked = !isActive; // Toggle'ı geri al
            }
        });
    });

    // Niyet silme
    document.querySelectorAll('.delete-intent').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Bu niyeti silmek istediğinizden emin misiniz?')) {
                return;
            }
            
            const intentId = this.dataset.intentId;
            
            try {
                const response = await fetch(`/api/intents/${intentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    showNotification(result.message, 'success');
                    document.querySelector(`[data-intent-id="${intentId}"]`).remove();
                }
            } catch (error) {
                showNotification('Silinirken hata oluştu', 'error');
            }
        });
    });
});

// Bildirim gösterme fonksiyonu
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}
</script>

<style>
.sector-filter {
    @apply bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors duration-200;
}

.sector-filter.active-sector {
    @apply bg-blue-500 text-white hover:bg-blue-600;
}

.sector-group {
    transition: all 0.3s ease;
}

.sector-group:hover {
    transform: translateY(-1px);
}

.create-from-template {
    transition: all 0.2s ease;
}

.create-from-template:hover {
    transform: scale(1.05);
}

/* Sektör ikonları için özel stiller */
.sector-icon {
    @apply w-8 h-8 rounded-lg flex items-center justify-center;
}

.sector-icon.ecommerce {
    @apply bg-blue-100 text-blue-600;
}

.sector-icon.real_estate {
    @apply bg-green-100 text-green-600;
}

.sector-icon.hotel {
    @apply bg-purple-100 text-purple-600;
}

.sector-icon.general {
    @apply bg-gray-100 text-gray-600;
}

/* Şablon kartları için hover efektleri */
.template-card {
    @apply border border-gray-200 rounded-lg p-4 transition-all duration-200;
}

.template-card:hover {
    @apply shadow-md transform -translate-y-1;
}

/* Yükleme animasyonu */
.loading-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Responsive tasarım için */
@media (max-width: 768px) {
    .sector-group {
        margin-bottom: 1.5rem;
    }
    
    .sector-filter {
        @apply text-xs px-2 py-1;
    }
}
</style>
@endpush 