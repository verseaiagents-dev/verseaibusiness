@extends('dashboard.partial.admin-layout')

@section('title', 'Agent Detayı - ' . $agent->name)
@section('description', $agent->name . ' agent detay bilgileri')

@section('content')
<div class="admin-content">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Agent Detayı</h1>
            <p class="text-gray-600">{{ $agent->name }} - {{ $agent->project->name ?? 'Proje Yok' }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.agents.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Geri Dön
            </a>
            <button onclick="editAgent({{ $agent->id }})" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Düzenle
            </button>
        </div>
    </div>

    <!-- Ana İstatistikler - Soldan Sağa -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Maliyet İstatistikleri -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Bugünkü Maliyet</p>
                    <p class="text-2xl font-bold">${{ number_format($agent->getTodayCost(), 4) }}</p>
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
                    <p class="text-sm opacity-90">Aylık Maliyet</p>
                    <p class="text-2xl font-bold">${{ number_format($agent->getMonthlyCost(), 4) }}</p>
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
                    <p class="text-2xl font-bold">${{ number_format($agent->getTotalCost(), 4) }}</p>
                </div>
                <div class="text-purple-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Toplam Kullanım</p>
                    <p class="text-2xl font-bold">{{ $agent->usageLogs->count() }}</p>
                </div>
                <div class="text-orange-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Genel İstatistikler - Soldan Sağa -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-yellow-500 mb-2">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $totalIntents }}</h3>
            <p class="text-xs text-gray-600">Toplam Niyet</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-red-500 mb-2">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $totalApiEvents }}</h3>
            <p class="text-xs text-gray-600">API Events</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-blue-500 mb-2">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $totalKnowledgeBase }}</h3>
            <p class="text-xs text-gray-600">Knowledge Base</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-gray-500 mb-2">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $agent->integrations->count() }}</h3>
            <p class="text-xs text-gray-600">Entegrasyonlar</p>
        </div>
    </div>

    <!-- Agent Bilgileri ve Detay Sekmeleri -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Agent Bilgileri -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Agent Bilgileri</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Agent Adı</label>
                            <p class="text-gray-900 font-medium">{{ $agent->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sektör</label>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $agent->sector }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                            @if($agent->is_active)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Pasif
                                </span>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kullanıcı</label>
                            <p class="text-gray-900 text-sm">{{ $agent->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $agent->user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Proje</label>
                            <p class="text-gray-900 text-sm">{{ $agent->project->name ?? 'Proje Yok' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Oluşturulma</label>
                            <p class="text-gray-900 text-sm">{{ $agent->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                        @if($agent->description)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
                            <p class="text-gray-900 text-sm">{{ $agent->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detay Sekmeleri -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <nav class="flex space-x-6" aria-label="Tabs">
                        <button class="tab-button active text-blue-600 border-b-2 border-blue-600 py-2 px-1 text-sm font-medium" data-tab="knowledge">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            Knowledge Base
                        </button>
                        <button class="tab-button text-gray-500 hover:text-gray-700 py-2 px-1 text-sm font-medium" data-tab="intents">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Niyetler
                        </button>
                        <button class="tab-button text-gray-500 hover:text-gray-700 py-2 px-1 text-sm font-medium" data-tab="api-events">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            API Events
                        </button>
                        <button class="tab-button text-gray-500 hover:text-gray-700 py-2 px-1 text-sm font-medium" data-tab="usage-logs">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Kullanım Logları
                        </button>
                    </nav>
                </div>
                
                <div class="p-6">
                    <!-- Knowledge Base Tab -->
                    <div id="knowledge" class="tab-content active">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-medium text-gray-900">Knowledge Base İçerikleri</h4>
                            <a href="{{ route('admin.agents.knowledge-base', $agent) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Yeni Ekle
                            </a>
                        </div>
                        @if($agent->project->knowledgeBase->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başlık</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İçerik</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oluşturulma</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($agent->project->knowledgeBase->take(5) as $item)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->title }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-500">{{ Str::limit($item->content, 50) }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->created_at->format('d.m.Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($agent->project->knowledgeBase->count() > 5)
                                <div class="mt-3 text-center">
                                    <a href="{{ route('admin.agents.knowledge-base', $agent) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                        Tümünü Görüntüle ({{ $agent->project->knowledgeBase->count() }})
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-6">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">Henüz knowledge base içeriği eklenmemiş.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Intents Tab -->
                    <div id="intents" class="tab-content hidden">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-medium text-gray-900">Agent Niyetleri</h4>
                            <a href="{{ route('admin.agents.intents', $agent) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Yeni Ekle
                            </a>
                        </div>
                        @if($agent->intents->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Niyet Adı</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Açıklama</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($agent->intents->take(5) as $intent)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $intent->name }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-500">{{ Str::limit($intent->description, 50) }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                @if($intent->is_active)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Pasif</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($agent->intents->count() > 5)
                                <div class="mt-3 text-center">
                                    <a href="{{ route('admin.agents.intents', $agent) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                        Tümünü Görüntüle ({{ $agent->intents->count() }})
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-6">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">Henüz niyet tanımlanmamış.</p>
                            </div>
                        @endif
                    </div>

                    <!-- API Events Tab -->
                    <div id="api-events" class="tab-content hidden">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-medium text-gray-900">API Events</h4>
                            <a href="{{ route('admin.agents.api-events', $agent) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Yeni Ekle
                            </a>
                        </div>
                        @if($agent->apiEvents->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Adı</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endpoint</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($agent->apiEvents->take(5) as $event)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $event->name }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $event->endpoint }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $event->method }}</span>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                @if($event->is_active)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Pasif</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($agent->apiEvents->count() > 5)
                                <div class="mt-3 text-center">
                                    <a href="{{ route('admin.agents.api-events', $agent) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                        Tümünü Görüntüle ({{ $agent->apiEvents->count() }})
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-6">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">Henüz API event tanımlanmamış.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Usage Logs Tab -->
                    <div id="usage-logs" class="tab-content hidden">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Kullanım Logları</h4>
                        @if($agent->usageLogs->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tarih</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Maliyet</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($agent->usageLogs->take(5) as $log)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $log->provider }}</span>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $log->model }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-green-600">${{ number_format($log->total_cost, 4) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($agent->usageLogs->count() > 5)
                                <div class="mt-3 text-center">
                                    <span class="text-gray-600 text-sm">
                                        Son 5 kayıt gösteriliyor (Toplam: {{ $agent->usageLogs->count() }})
                                    </span>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-6">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <p class="text-gray-500 text-sm">Henüz kullanım logu bulunmuyor.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editAgent(agentId) {
    // Agent düzenleme modalını aç
    window.location.href = `/admin/agents/${agentId}/edit`;
}

// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'text-blue-600', 'border-blue-600');
                btn.classList.add('text-gray-500', 'hover:text-gray-700');
            });
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            
            // Add active class to clicked button and target content
            this.classList.add('active', 'text-blue-600', 'border-blue-600');
            this.classList.remove('text-gray-500', 'hover:text-gray-700');
            document.getElementById(targetTab).classList.remove('hidden');
            document.getElementById(targetTab).classList.add('active');
        });
    });
});
</script>
@endsection 