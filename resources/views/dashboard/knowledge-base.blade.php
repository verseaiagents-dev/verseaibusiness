<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('admin.knowledge_base') }} - {{ $knowledgeBaseData['project']->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <link href="{{ asset('dashboard/assets/css/tailwind.css') }}" rel="stylesheet">
    <link href="{{ asset('dashboard/assets/css/custom-dashboard.css') }}" rel="stylesheet">
    
    <!-- Icons -->
    <link href="{{ asset('dashboard/assets/css/icons.css') }}" rel="stylesheet">
    
    <!-- Tailwind CSS Fixes - Load last to ensure proper color preservation -->
    <link href="{{ asset('dashboard/assets/css/tailwind-fixes.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="dashboard-layout">
        <!-- Sidebar -->
        @include('dashboard.partial.sidebar')

        <!-- Mobile Toggle -->
        <button id="mobileToggle" class="mobile-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Main Content -->
        <main id="dashboardMain" class="dashboard-main">
            <div class="dashboard-content">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ __('admin.knowledge_base') }}</h1>
                        <p class="text-gray-600 mt-1">{{ $knowledgeBaseData['project']->name }} {{ __('admin.knowledge_base_description') }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            {{ __('admin.back_to_dashboard') }}
                        </a>
                    </div>
                </div>

                <!-- Knowledge Base Stats -->
                <div class="flex flex-col md:flex-row gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white text-sm font-medium">{{ __('admin.total_documents') }}</p>
                                <p class="text-3xl font-bold">{{ $knowledgeBaseData['total_documents'] }}</p>
                            </div>
                            <div class="bg-blue-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white text-sm font-medium">{{ __('admin.last_update') }}</p>
                                <p class="text-3xl font-bold">{{ $knowledgeBaseData['last_updated'] ? \Carbon\Carbon::parse($knowledgeBaseData['last_updated'])->format('d/m') : '--' }}</p>
                            </div>
                            <div class="bg-green-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white flex-1">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white text-sm font-medium">{{ __('admin.project_status') }}</p>
                                <p class="text-3xl font-bold">{{ ucfirst($knowledgeBaseData['project']->status) }}</p>
                            </div>
                            <div class="bg-purple-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Knowledge Base Content -->
                <div class="dashboard-content">
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200 mb-8">
                        <nav class="-mb-px flex space-x-8">
                                <button onclick="knowledgeBaseManager.switchTab('documents')" 
                                class="tab-button active py-2 px-1 border-b-2 font-medium text-sm" 
                                        data-tab="documents">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ __('admin.documents') }}
                                </button>
                                <button onclick="knowledgeBaseManager.switchTab('agent')" 
                                class="tab-button py-2 px-1 border-b-2 font-medium text-sm" 
                                        data-tab="agent">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-1a7 7 0 00-7-7H9a7 7 0 00-7 7v1h5m4-5a4 4 0 100-8 4 4 0 000 8z" />
                                    </svg>
                                {{ __('admin.agents') }}
                                </button>
                            </nav>
                    </div>

                    <!-- Documents Tab Content -->
                    <div id="documents-tab" class="tab-content active">
                        <!-- Import Options Section -->
                        <div class="mb-8">
                         <h2 class="text-2xl font-bold text-gray-900">{{ __('admin.importoptions') }}</h2>


                        <div class="flex flex-col gap-4">
                            <!-- First Row -->
                            <div class="flex flex-row gap-4">
                                <!-- CSV Import -->
                                <div class="flex-1 bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow cursor-pointer" onclick="knowledgeBaseManager.openImportModal('csv')">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-blue-100 rounded-lg p-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 018.25 20.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                            </svg>
                                        </div>
                                        <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">CSV</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-2">CSV Import</h4>
                                    <p class="text-sm text-gray-600">Import structured data from CSV files to your knowledge base.</p>
                                </div>
                                <!-- PDF Import -->
                                <div class="flex-1 bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow cursor-pointer" onclick="knowledgeBaseManager.openImportModal('pdf')">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-red-100 rounded-lg p-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-red-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                            </svg>
                                        </div>
                                        <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">PDF</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-2">PDF Import</h4>
                                    <p class="text-sm text-gray-600">Import knowledge from PDF documents and extract text content.</p>
                                </div>
                                <!-- XML Import -->
                                <div class="flex-1 bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow cursor-pointer" onclick="knowledgeBaseManager.openImportModal('xml')">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-green-100 rounded-lg p-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-green-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                            </svg>
                                        </div>
                                        <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">XML</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-2">XML Import</h4>
                                    <p class="text-sm text-gray-600">Import structured data from XML files and sitemaps.</p>
                                </div>
                            </div>
                            <!-- Second Row -->
                            <div class="flex flex-row gap-4">
                                <!-- Sitemap Import -->
                                <div class="flex-1 bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow cursor-pointer" onclick="knowledgeBaseManager.openImportModal('sitemap')">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-purple-100 rounded-lg p-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-purple-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.959 8.959 0 018.716 6.747M12 3a8.959 8.959 0 00-8.716 6.747" />
                                            </svg>
                                        </div>
                                        <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">Sitemap</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Sitemap Import</h4>
                                    <p class="text-sm text-gray-600">Import content from website sitemaps automatically.</p>
                                </div>
                                <!-- Website Scraping -->
                                <div class="flex-1 bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow cursor-pointer" onclick="knowledgeBaseManager.openImportModal('scraping')">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-orange-100 rounded-lg p-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-orange-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.959 8.959 0 018.716 6.747M12 3a8.959 8.959 0 00-8.716 6.747" />
                                            </svg>
                                        </div>
                                        <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-1 rounded-full">Scraping</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-2">Website Scraping</h4>
                                    <p class="text-sm text-gray-600">Scrape content from websites and import to knowledge base.</p>
                                </div>
                                <!-- URL Import -->
                                <div class="flex-1 bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow cursor-pointer" onclick="knowledgeBaseManager.openImportModal('url')">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="bg-indigo-100 rounded-lg p-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-indigo-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                                            </svg>
                                        </div>
                                        <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full">URL</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-900 mb-2">URL Import</h4>
                                    <p class="text-sm text-gray-600">Import content from specific URLs and web pages.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mb-6">
                         <div>
                             <h2 class="text-2xl font-bold text-gray-900">{{ __('admin.trained_data') }}</h2>
                             <p class="text-gray-600 mt-1">{{ __('admin.trained_data_description') }}</p>
                         </div>
                     </div>
                    @if($knowledgeBaseData['total_documents'] > 0)
                        <!-- Documents List -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($knowledgeBaseData['documents'] as $document)
                                <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="bg-blue-100 rounded-lg p-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-600">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-900">{{ $document->file_name }}</h3>
                                                <p class="text-sm text-gray-500">{{ number_format($document->file_size / 1024, 1) }} KB</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ ucfirst($document->status) }}
                                            </span>
                                            <button onclick="knowledgeBaseManager.deleteDocument({{ $document->id }})" 
                                                class="text-red-500 hover:text-red-700 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- AI Processing Status -->
                                    <div class="mb-4">
                                        @if($document->ai_processing_status)
                                            <div class="flex items-center space-x-2 mb-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    @if($document->ai_processing_status === 'completed') bg-green-100 text-green-800
                                                    @elseif($document->ai_processing_status === 'processing') bg-yellow-100 text-yellow-800
                                                    @elseif($document->ai_processing_status === 'failed') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    AI: {{ ucfirst($document->ai_processing_status) }}
                                                </span>
                                            </div>
                                            
                                            @if($document->ai_processing_status === 'completed' && $document->ai_summary)
                                                <div class="bg-gray-50 rounded-lg p-3 mb-3">
                                                    <p class="text-sm text-gray-700 font-medium mb-1">AI Özeti:</p>
                                                    <p class="text-xs text-gray-600">{{ $document->getShortSummary(150) }}</p>
                                                </div>
                                            @endif
                                            
                                            @if($document->ai_categories && count($document->ai_categories) > 0)
                                                <div class="flex flex-wrap gap-1 mb-3">
                                                    @foreach(array_slice($document->ai_categories, 0, 3) as $category)
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $category }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    
                                    <div class="text-sm text-gray-600 mb-4">
                                        <p><strong>{{ __('admin.document_type') }}:</strong> {{ strtoupper(pathinfo($document->file_name, PATHINFO_EXTENSION)) }}</p>
                                        <p><strong>{{ __('admin.upload_date') }}:</strong> {{ $document->created_at->format('d.m.Y H:i') }}</p>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex space-x-2">
                                            <button onclick="knowledgeBaseManager.downloadDocument({{ $document->id }})" 
                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                                {{ __('admin.download') }}
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ml-1">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                </svg>
                                            </button>
                                            
                                            @if($document->ai_processing_status !== 'completed' && $document->ai_processing_status !== 'processing')
                                                <button onclick="knowledgeBaseManager.processWithAi({{ $document->id }})" 
                                                    class="text-purple-600 hover:text-purple-800 text-sm font-medium flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423L16.5 15.75l.394 1.183a2.25 2.25 0 001.423 1.423L19.5 18.75l-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                                    </svg>
                                                    AI İşle
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-gray-400 mb-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('admin.no_documents_uploaded') }}</h3>
                            <p class="text-gray-600 mb-4">{{ __('admin.no_documents_description') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Agent Tab Content -->
                    <div id="agent-tab" class="tab-content hidden">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Agent Yönetimi</h2>
                            <p class="text-gray-600 mt-1">AI Agent'ınızın niyet ve eylemlerini yönetin</p>
                    </div>

                    <!-- Agent Stats -->
                        <div class="flex flex-col md:flex-row gap-6 mb-8">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white text-sm font-medium">Aktif Niyetler</p>
                                        <p class="text-3xl font-bold" id="active-intents-count">0</p>
                                    </div>
                                    <div class="bg-blue-400 rounded-lg p-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-white text-sm font-medium">API Eventler</p>
                                        <p class="text-3xl font-bold" id="api-events-count">0</p>
                                    </div>
                                    <div class="bg-green-400 rounded-lg p-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white flex-1">
                                <div class="flex items-center justify-between">
                                <div>
                                        <p class="text-white text-sm font-medium">Toplam Agent</p>
                                        <p class="text-3xl font-bold" id="total-agents-count">0</p>
                                </div>
                                    <div class="bg-purple-400 rounded-lg p-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-1a7 7 0 00-7-7H9a7 7 0 00-7 7v1h5m4-5a4 4 0 100-8 4 4 0 000 8z" />
                                        </svg>
                            </div>
                        </div>
                        </div>
                    </div>

                        <!-- Agent Management Sections -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Intent Management -->
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-xl font-semibold text-gray-900">Niyet Yönetimi</h3>
                                    <button onclick="knowledgeBaseManager.openIntentModal()" 
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Yeni Niyet
                                </button>
                            </div>
                                <div id="intents-list" class="space-y-3">
                            <!-- Intents will be loaded here -->
                        </div>
                    </div>

                            <!-- API Event Management -->
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-xl font-semibold text-gray-900">API Event Yönetimi</h3>
                                    <button onclick="knowledgeBaseManager.openApiEventModal()" 
                                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Yeni Event
                                </button>
                            </div>
                                <div id="api-events-list" class="space-y-3">
                            <!-- API Events will be loaded here -->
                        </div>
                    </div>
                </div>
                        </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        class KnowledgeBaseManager {
            constructor() {
                this.init();
                this.currentProjectId = window.location.pathname.split('/').pop();
            }

            init() {
                this.bindEvents();
                this.loadAgentData();
            }

            bindEvents() {
                // Tab switching
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.addEventListener('click', (e) => {
                        this.switchTab(e.target.dataset.tab);
                    });
                });
            }

            switchTab(tabName) {
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });

                // Remove active class from all tab buttons
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.classList.remove('active', 'border-blue-500', 'text-blue-600');
                    button.classList.add('border-transparent', 'text-gray-500');
                });

                // Show selected tab content
                document.getElementById(`${tabName}-tab`).classList.remove('hidden');

                // Add active class to selected tab button
                const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
                activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
                activeButton.classList.remove('border-transparent', 'text-gray-500');

                // Load data if switching to agent tab
                if (tabName === 'agent') {
                    this.loadAgentData();
                }
            }

            showSuccess(message) {
                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                successDiv.textContent = message;
                
                document.body.appendChild(successDiv);
                
                setTimeout(() => {
                    successDiv.remove();
                }, 5000);
            }

            showError(message) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                errorDiv.textContent = message;
                
                document.body.appendChild(errorDiv);
                
                setTimeout(() => {
                    errorDiv.remove();
                }, 5000);
            }

            async deleteDocument(documentId) {
                if (!confirm('{{ __("admin.confirm_delete_document") }}')) {
                    return;
                }

                const projectId = window.location.pathname.split('/').pop();

                try {
                    const response = await fetch(`/api/projects/${projectId}/knowledge-base/${documentId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showSuccess('{{ __("admin.document_deleted_successfully") }}');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showError(data.message || '{{ __("admin.document_deletion_failed") }}');
                    }
                } catch (error) {
                    console.error('Error deleting document:', error);
                    this.showError('{{ __("admin.document_deletion_error") }}');
                }
            }

            async downloadDocument(documentId) {
                const projectId = window.location.pathname.split('/').pop();

                try {
                    const response = await fetch(`/api/projects/${projectId}/knowledge-base/${documentId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Create download link
                        const link = document.createElement('a');
                        link.href = `/api/projects/${projectId}/knowledge-base/${documentId}/download`;
                        link.download = data.data.file_name;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        this.showError(data.message || '{{ __("admin.document_download_failed") }}');
                    }
                } catch (error) {
                    console.error('Error downloading document:', error);
                    this.showError('{{ __("admin.document_download_error") }}');
                }
            }

            async processWithAi(documentId) {
                const projectId = window.location.pathname.split('/').pop();

                try {
                    // Show processing indicator
                    this.showSuccess('AI işleme başlatılıyor...');

                    const response = await fetch(`/api/projects/${projectId}/knowledge-base/${documentId}/process-ai`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showSuccess('AI işleme başarıyla tamamlandı!');
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        this.showError(data.message || 'AI işleme başarısız');
                    }
                } catch (error) {
                    console.error('Error processing with AI:', error);
                    this.showError('AI işleme sırasında hata oluştu');
                }
            }

            openImportModal(type) {
                const projectId = window.location.pathname.split('/').pop();
                const modal = this.createImportModal(type, projectId);
                document.body.appendChild(modal);
            }

            createImportModal(type, projectId) {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Import ${type.toUpperCase()}</h3>
                                <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <form id="importForm" class="space-y-4">
                                ${this.getImportFormFields(type)}
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button type="button" onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Import
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;

                // Add form submit handler
                const form = modal.querySelector('#importForm');
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.handleImportSubmit(type, projectId, form);
                });

                return modal;
            }

            getImportFormFields(type) {
                const fields = {
                    csv: `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                            <input type="file" name="file" accept=".csv" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    `,
                    pdf: `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">PDF File</label>
                            <input type="file" name="file" accept=".pdf" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    `,
                    xml: `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">XML File</label>
                            <input type="file" name="file" accept=".xml" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    `,
                    sitemap: `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sitemap URL</label>
                            <input type="url" name="sitemap_url" placeholder="https://example.com/sitemap.xml" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    `,
                    scraping: `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Website URL</label>
                            <input type="url" name="website_url" placeholder="https://example.com" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Pages</label>
                            <input type="number" name="max_pages" value="10" min="1" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    `,
                    url: `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                            <input type="url" name="url" placeholder="https://example.com/page" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    `
                };
                return fields[type] || '';
            }

            async handleImportSubmit(type, projectId, form) {
                const formData = new FormData(form);
                formData.append('type', type);

                try {
                    const response = await fetch(`/api/projects/${projectId}/knowledge-base/import`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showSuccess('Import başarıyla tamamlandı!');
                        form.closest('.fixed').remove();
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        let errorMessage = data.message || 'Import başarısız';
                        if (data.errors) {
                            errorMessage += ': ' + Object.values(data.errors).flat().join(', ');
                        }
                        this.showError(errorMessage);
                    }
                } catch (error) {
                    console.error('Error importing:', error);
                    this.showError('Import sırasında hata oluştu: ' + error.message);
                }
            }

            // Agent Management Functions
        async loadAgentData() {
            try {
                    const response = await fetch(`/api/projects/${this.currentProjectId}/agent-data`, {
                        method: 'GET',
                    headers: {
                            'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    this.updateAgentStats(data.stats);
                        this.renderIntents(data.intents);
                        this.renderApiEvents(data.apiEvents);
                    } else {
                        this.showError(data.message || 'Agent verileri yüklenemedi');
                }
            } catch (error) {
                console.error('Error loading agent data:', error);
                    this.showError('Agent verileri yüklenirken hata oluştu');
            }
        }

        updateAgentStats(stats) {
            document.getElementById('active-intents-count').textContent = stats.activeIntents || 0;
            document.getElementById('api-events-count').textContent = stats.apiEvents || 0;
                document.getElementById('total-agents-count').textContent = stats.totalAgents || 0;
        }

            renderIntents(intents) {
            const container = document.getElementById('intents-list');
            container.innerHTML = '';

            if (intents.length === 0) {
                container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                            <p>Henüz niyet tanımlanmamış</p>
                    </div>
                `;
                return;
            }

            intents.forEach(intent => {
                    const intentElement = document.createElement('div');
                    intentElement.className = 'bg-gray-50 rounded-lg p-4 border border-gray-200';
                    intentElement.innerHTML = `
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-gray-900">${intent.name}</h4>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${intent.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                ${intent.is_active ? 'Aktif' : 'Pasif'}
                            </span>
                        <button onclick="knowledgeBaseManager.editIntent(${intent.id})" class="text-blue-600 hover:text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </button>
                    </div>
                </div>
                        <p class="text-sm text-gray-600 mb-2">${intent.description || 'Açıklama yok'}</p>
                        <div class="flex flex-wrap gap-1">
                            ${(intent.config?.keywords || []).slice(0, 3).map(keyword => 
                                `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${keyword}</span>`
                            ).join('')}
                            ${(intent.config?.keywords || []).length > 3 ? 
                                `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">+${(intent.config?.keywords || []).length - 3} daha</span>` : ''
                            }
                </div>
            `;
                    container.appendChild(intentElement);
                });
        }

            renderApiEvents(apiEvents) {
            const container = document.getElementById('api-events-list');
            container.innerHTML = '';

            if (apiEvents.length === 0) {
                container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                            <p>Henüz API event tanımlanmamış</p>
                    </div>
                `;
                return;
            }

            apiEvents.forEach(event => {
                    const eventElement = document.createElement('div');
                    eventElement.className = 'bg-gray-50 rounded-lg p-4 border border-gray-200';
                    eventElement.innerHTML = `
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-gray-900">${event.name}</h4>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ${event.http_method || 'N/A'}
                            </span>
                        <button onclick="knowledgeBaseManager.editApiEvent(${event.id})" class="text-blue-600 hover:text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </button>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">${event.description || 'Açıklama yok'}</p>
                        <div class="text-xs text-gray-500">
                            <span class="font-medium">Endpoint:</span> ${event.endpoint_url || 'N/A'}
                        </div>
                    `;
                    container.appendChild(eventElement);
                });
            }

            openIntentModal() {
                const modal = this.createIntentModal();
                document.body.appendChild(modal);
            }

            openApiEventModal() {
                const modal = this.createApiEventModal();
                document.body.appendChild(modal);
            }

            createIntentModal() {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Yeni Niyet Ekle</h3>
                                <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                            </div>
                            
                            <!-- Sector Templates Section -->
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('admin.sector_templates') }}</h4>
                                <div id="sector-templates" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <!-- Templates will be loaded here -->
                                </div>
                            </div>
                            
                            <form id="intentForm" class="space-y-4" onsubmit="knowledgeBaseManager.handleIntentSubmit(this); return false;">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Niyet Adı</label>
                                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Anahtar Kelimeler (virgülle ayırın)</label>
                                    <input type="text" name="keywords" placeholder="sepete ekle, satın al, ürün ara" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button type="button" onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                        İptal
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Kaydet
                                    </button>
                                </div>
                            </form>
                    </div>
                </div>
            `;

                // Load sector templates
                this.loadSectorTemplates(modal);

                return modal;
            }

            createApiEventModal() {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Yeni API Event Ekle</h3>
                                <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <form id="apiEventForm" class="space-y-4" onsubmit="knowledgeBaseManager.handleApiEventSubmit(this); return false;">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Event Adı</label>
                                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">İlişkili Niyet</label>
                                    <select name="intent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Niyet Seçin (Opsiyonel)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">HTTP Metodu</label>
                                    <select name="method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="GET">GET</option>
                                        <option value="POST">POST</option>
                                        <option value="PUT">PUT</option>
                                        <option value="DELETE">DELETE</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Endpoint URL</label>
                                    <input type="url" name="endpoint" placeholder="https://api.example.com/endpoint" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button type="button" onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                        İptal
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                        Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;

                // Load intents into dropdown
                this.loadIntentsForApiEvent(modal);

                return modal;
            }

            async handleIntentSubmit(form) {
                const formData = new FormData(form);
                const intentData = {
                    name: formData.get('name'),
                    description: formData.get('description'),
                    keywords: formData.get('keywords').split(',').map(k => k.trim()).filter(k => k),
                    project_id: this.currentProjectId
                };

                // Add template data if available
                if (form.dataset.templateKey) {
                    intentData.response_type = form.dataset.templateKey;
                    intentData.actions = JSON.parse(form.dataset.templateActions || '[]');
                }

                try {
                    const response = await fetch(`/api/projects/${this.currentProjectId}/intents`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(intentData)
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('Server response:', errorText);
                        throw new Error(`HTTP ${response.status}: ${errorText}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        this.showSuccess('Niyet başarıyla eklendi!');
                        form.closest('.fixed').remove();
                        this.loadAgentData();
                    } else {
                        this.showError(data.message || 'Niyet eklenemedi');
                    }
                } catch (error) {
                    console.error('Error creating intent:', error);
                    this.showError('Niyet eklenirken hata oluştu: ' + error.message);
                }
            }

            async handleApiEventSubmit(form) {
                const formData = new FormData(form);
                const eventData = {
                    name: formData.get('name'),
                    description: formData.get('description'),
                    method: formData.get('method'),
                    endpoint: formData.get('endpoint'),
                    intent_id: formData.get('intent_id') || null,
                    project_id: this.currentProjectId
                };

                try {
                    const response = await fetch(`/api/projects/${this.currentProjectId}/api-events`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(eventData)
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('Server response:', errorText);
                        throw new Error(`HTTP ${response.status}: ${errorText}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        this.showSuccess('API Event başarıyla eklendi!');
                        form.closest('.fixed').remove();
                        this.loadAgentData();
                    } else {
                        this.showError(data.message || 'API Event eklenemedi');
                    }
                } catch (error) {
                    console.error('Error creating API event:', error);
                    this.showError('API Event eklenirken hata oluştu: ' + error.message);
                }
            }

            editIntent(intentId) {
                // Implementation for editing intent
                this.showError('Düzenleme özelliği yakında eklenecek');
        }

        editApiEvent(eventId) {
            // Implementation for editing API event
                this.showError('Düzenleme özelliği yakında eklenecek');
            }

            async loadSectorTemplates(modal) {
                try {
                    const response = await fetch(`/api/projects/${this.currentProjectId}/sector-templates`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.renderSectorTemplates(modal, data.templates, data.sector);
                    } else {
                        this.showError(data.message || '{{ __("admin.templates_loading_failed") }}');
                    }
                } catch (error) {
                    console.error('Error loading sector templates:', error);
                    this.showError('{{ __("admin.templates_loading_error") }}');
                }
            }

            renderSectorTemplates(modal, templates, sector) {
                const container = modal.querySelector('#sector-templates');
                container.innerHTML = '';

                Object.entries(templates).forEach(([key, template]) => {
                    const templateElement = document.createElement('div');
                    templateElement.className = 'bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-blue-300 cursor-pointer transition-colors';
                    templateElement.innerHTML = `
                        <div class="flex items-start justify-between mb-2">
                            <h5 class="font-medium text-gray-900">${template.name}</h5>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${sector}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">${template.description}</p>
                        <div class="flex flex-wrap gap-1 mb-3">
                            ${template.keywords.slice(0, 3).map(keyword => 
                                `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">${keyword}</span>`
                            ).join('')}
                            ${template.keywords.length > 3 ? 
                                `<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">+${template.keywords.length - 3} daha</span>` : ''
                            }
                        </div>
                        <button type="button" class="w-full text-sm text-blue-600 hover:text-blue-800 font-medium">
                            {{ __('admin.use_template') }}
                        </button>
                    `;

                    // Add click event to fill form with template data
                    templateElement.addEventListener('click', () => {
                        this.fillIntentFormWithTemplate(modal, template);
                    });

                    container.appendChild(templateElement);
                });
            }

            fillIntentFormWithTemplate(modal, template) {
                const form = modal.querySelector('#intentForm');
                form.querySelector('input[name="name"]').value = template.name;
                form.querySelector('textarea[name="description"]').value = template.description;
                form.querySelector('input[name="keywords"]').value = template.keywords.join(', ');

                // Add template data to form for submission
                form.dataset.templateKey = template.response_type;
                form.dataset.templateActions = JSON.stringify(template.actions);

                // Show success message
                this.showSuccess('{{ __("admin.template_loaded") }}');
            }

            async loadIntentsForApiEvent(modal) {
                try {
                    const response = await fetch(`/api/projects/${this.currentProjectId}/agent-data`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    const data = await response.json();
                    
                    if (data.success && data.intents) {
                        const select = modal.querySelector('select[name="intent_id"]');
                        
                        // Clear existing options except the first one
                        while (select.children.length > 1) {
                            select.removeChild(select.lastChild);
                        }
                        
                        // Add intent options
                        data.intents.forEach(intent => {
                            const option = document.createElement('option');
                            option.value = intent.id;
                            option.textContent = intent.name;
                            select.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error('Error loading intents for API event:', error);
                }
            }

            async editIntent(intentId) {
                try {
                    // Get intent data
                    const response = await fetch(`/api/projects/${this.currentProjectId}/intents/${intentId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        const modal = this.createEditIntentModal(data.intent);
                        document.body.appendChild(modal);
                    } else {
                        this.showError(data.message || 'Niyet bilgileri yüklenemedi');
                    }
                } catch (error) {
                    console.error('Error loading intent for edit:', error);
                    this.showError('Niyet bilgileri yüklenirken hata oluştu');
                }
            }

            createEditIntentModal(intent) {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Niyet Düzenle</h3>
                                <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <form id="editIntentForm" class="space-y-4" onsubmit="knowledgeBaseManager.handleEditIntentSubmit(this, ${intent.id}); return false;">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Niyet Adı</label>
                                    <input type="text" name="name" value="${intent.name}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">${intent.description || ''}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Anahtar Kelimeler (virgülle ayırın)</label>
                                    <input type="text" name="keywords" value="${(intent.config?.keywords || []).join(', ')}" placeholder="sepete ekle, satın al, ürün ara" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Durum</label>
                                    <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="1" ${intent.is_active ? 'selected' : ''}>Aktif</option>
                                        <option value="0" ${!intent.is_active ? 'selected' : ''}>Pasif</option>
                                    </select>
                                </div>
                                <div class="flex justify-end space-x-3 pt-4">
                                    <button type="button" onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                        İptal
                                    </button>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Güncelle
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;

                return modal;
            }

            async handleEditIntentSubmit(form, intentId) {
                const formData = new FormData(form);
                const intentData = {
                    name: formData.get('name'),
                    description: formData.get('description'),
                    keywords: formData.get('keywords').split(',').map(k => k.trim()).filter(k => k),
                    is_active: formData.get('is_active') === '1',
                    project_id: this.currentProjectId
                };

                try {
                    const response = await fetch(`/api/projects/${this.currentProjectId}/intents/${intentId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(intentData)
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        this.showSuccess('Niyet başarıyla güncellendi');
                        form.closest('.fixed').remove();
                        this.loadAgentData(); // Reload the data
                    } else {
                        this.showError(data.message || 'Niyet güncellenirken hata oluştu');
                    }
                } catch (error) {
                    console.error('Error updating intent:', error);
                    this.showError('Niyet güncellenirken hata oluştu');
                }
            }
        }

        // Initialize knowledge base manager
        const knowledgeBaseManager = new KnowledgeBaseManager();
    </script>

    <!-- Scripts -->
    <script src="{{ asset('dashboard/assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('dashboard/assets/libs/@popperjs/core/umd/popper.min.js') }}"></script>
    <script src="{{ asset('dashboard/assets/libs/tippy.js/tippy-bundle.umd.min.js') }}"></script>
    <script src="{{ asset('dashboard/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('dashboard/assets/libs/prismjs/prism.js') }}"></script>
    <script src="{{ asset('dashboard/assets/libs/lucide/umd/lucide.js') }}"></script>
    <script src="{{ asset('dashboard/assets/js/tailwick.bundle.js') }}"></script>
    <script src="{{ asset('dashboard/assets/js/app.js') }}"></script>
    
    <style>
        /* Tab Styles */
        .tab-button {
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            border-color: #3b82f6;
            color: #2563eb;
        }
        
        .tab-button:not(.active) {
            border-color: transparent;
            color: #6b7280;
        }
        
        .tab-button:hover:not(.active) {
            color: #374151;
        }
        
        .tab-content {
            transition: opacity 0.3s ease;
        }
        
        .tab-content.hidden {
            display: none;
        }
        
        /* Agent Management Styles */
        .intent-card, .api-event-card {
            transition: all 0.2s ease;
        }
        
        .intent-card:hover, .api-event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</body>
</html> 