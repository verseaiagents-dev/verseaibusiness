@extends('dashboard.partial.user-layout')

@section('title', 'Niyet Yönetimi - VersAI')
@section('description', 'AI Niyet ve Eylem Yönetimi')

@section('content')
<div class="admin-content">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Niyet Yönetimi</h1>
            <p class="text-gray-600 mt-1">AI Agent'larınızın niyet ve eylemlerini yönetin</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('user.api-events.index') }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                API Event Yönetimi
            </a>
        </div>
    </div>

    <!-- İstatistikler - Yatay Görünüm -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        @php
            $totalAgents = $agentsBySector->flatten()->count();
            $totalIntents = $agentsBySector->flatten()->sum(function($agent) { return $agent->intents->count(); });
            $activeIntents = $agentsBySector->flatten()->sum(function($agent) { return $agent->intents->where('is_active', true)->count(); });
            $totalApiEvents = $agentsBySector->flatten()->sum(function($agent) { return $agent->apiEvents->count(); });
        @endphp
        
        <!-- Desktop Yatay Görünüm -->
        <div class="hidden md:flex items-center justify-between">
            <!-- Toplam Agent -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Toplam Agent</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalAgents }}</p>
                </div>
            </div>

            <!-- Ayırıcı -->
            <div class="w-px h-16 bg-gray-200"></div>

            <!-- Toplam Niyet -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Toplam Niyet</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalIntents }}</p>
                </div>
            </div>

            <!-- Ayırıcı -->
            <div class="w-px h-16 bg-gray-200"></div>

            <!-- Aktif Niyet -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Aktif Niyet</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $activeIntents }}</p>
                </div>
            </div>

            <!-- Ayırıcı -->
            <div class="w-px h-16 bg-gray-200"></div>

            <!-- API Event -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">API Event</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalApiEvents }}</p>
                </div>
            </div>
        </div>

        <!-- Mobil Grid Görünüm -->
        <div class="md:hidden grid grid-cols-2 gap-4">
            <!-- Toplam Agent -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">Toplam Agent</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalAgents }}</p>
                </div>
            </div>

            <!-- Toplam Niyet -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">Toplam Niyet</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalIntents }}</p>
                </div>
            </div>

            <!-- Aktif Niyet -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">Aktif Niyet</p>
                    <p class="text-xl font-bold text-gray-900">{{ $activeIntents }}</p>
                </div>
            </div>

            <!-- API Event -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">API Event</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalApiEvents }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sektör Bazlı Agent'lar -->
    @foreach($agentsBySector as $sector => $agents)
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ ucfirst($sector) }} Sektörü
                    <span class="text-sm font-normal text-gray-500">({{ $agents->count() }} agent)</span>
                </h2>
                <div class="flex space-x-2">
                    <span class="px-3 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                        {{ $sector == 'ecommerce' ? 'E-Ticaret' : ($sector == 'real_estate' ? 'Emlak' : ($sector == 'hotel' ? 'Otel' : ucfirst($sector))) }}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($agents as $agent)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-medium text-gray-900">{{ $agent->name }}</h3>
                        <span class="px-2 py-1 text-xs rounded {{ $agent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $agent->is_active ? 'Aktif' : 'Pasif' }}
                        </span>
                    </div>
                    
                    @if($agent->description)
                    <p class="text-sm text-gray-600 mb-3">{{ $agent->description }}</p>
                    @endif
                    
                    <!-- Sektör Bazlı Niyet Örnekleri -->
                    <div class="mb-3">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Sektör Niyetleri:</h4>
                        <div class="space-y-1">
                            @if($sector == 'ecommerce')
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">Ürün Sepete Ekle</span>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded">API</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">Sipariş Durumu</span>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">GET</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">Stok Kontrolü</span>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">POST</span>
                                </div>
                            @elseif($sector == 'real_estate')
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">Emlak Arama</span>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded">API</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">Randevu Talebi</span>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">POST</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">Fiyat Hesaplama</span>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">GET</span>
                                </div>
                            @elseif($sector == 'hotel')
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">Oda Rezervasyonu</span>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded">API</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">Müsaitlik Kontrolü</span>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">GET</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">Fiyat Sorgulama</span>
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded">POST</span>
                                </div>
                            @else
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600">Genel Sorgu</span>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded">API</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                        <span>{{ $agent->intents->where('is_active', true)->count() }} aktif niyet</span>
                        <span>{{ $agent->apiEvents->count() }} API event</span>
                    </div>
                    
                    <div class="space-y-2">
                        @foreach($agent->intents->take(3) as $intent)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <span class="text-sm text-gray-700">{{ ucfirst(str_replace('_', ' ', $intent->name)) }}</span>
                            <span class="text-xs px-2 py-1 rounded {{ $intent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $intent->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </div>
                        @endforeach
                        
                        @if($agent->intents->count() > 3)
                        <div class="text-center">
                            <span class="text-xs text-gray-500">+{{ $agent->intents->count() - 3 }} daha</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
                        <a href="{{ route('user.intents.show', $agent) }}" 
                           class="w-full bg-blue-500 text-white text-center py-2 px-4 rounded hover:bg-blue-600 transition-colors">
                            Niyetleri Yönet
                        </a>
                        <a href="{{ route('user.api-events.show', $agent) }}" 
                           class="w-full bg-green-500 text-white text-center py-2 px-4 rounded hover:bg-green-600 transition-colors">
                            API Event Yönet
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

    @if($agentsBySector->isEmpty())
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz Agent Oluşturmadınız</h3>
        <p class="text-gray-600 mb-4">AI Agent'larınızı oluşturarak niyet yönetimine başlayabilirsiniz.</p>
        <a href="{{ route('admin.ai-settings') }}" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600">
            Agent Oluştur
        </a>
    </div>
    @endif

    <!-- API Entegrasyon Rehberi -->
    <div class="bg-white rounded-lg shadow mt-8">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">API Entegrasyon Rehberi</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">E-Ticaret Sektörü</h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p>• Ürün sepete ekleme (POST)</p>
                        <p>• Sipariş durumu sorgulama (GET)</p>
                        <p>• Stok kontrolü (GET)</p>
                        <p>• Fiyat hesaplama (POST)</p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Emlak Sektörü</h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p>• Emlak arama (GET)</p>
                        <p>• Randevu talebi (POST)</p>
                        <p>• Fiyat hesaplama (GET)</p>
                        <p>• Müsaitlik kontrolü (GET)</p>
                    </div>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Otel Sektörü</h3>
                    <div class="space-y-2 text-sm text-gray-600">
                        <p>• Oda rezervasyonu (POST)</p>
                        <p>• Müsaitlik kontrolü (GET)</p>
                        <p>• Fiyat sorgulama (GET)</p>
                        <p>• Rezervasyon iptali (DELETE)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 