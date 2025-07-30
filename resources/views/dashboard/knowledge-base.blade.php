<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Base - {{ $knowledgeBaseData['project']->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <link href="{{ asset('dashboard/assets/css/tailwind.css') }}" rel="stylesheet">
    <link href="{{ asset('dashboard/assets/css/custom-dashboard.css') }}" rel="stylesheet">
    
    <!-- Icons -->
    <link href="{{ asset('dashboard/assets/css/icons.css') }}" rel="stylesheet">
    
    <!-- Custom Dashboard CSS -->
    <link href="{{ asset('dashboard/assets/css/custom-dashboard.css') }}" rel="stylesheet">
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100">{{ __('admin.total_documents') }}</p>
                                <p class="text-3xl font-bold">{{ $knowledgeBaseData['total_documents'] }}</p>
                            </div>
                            <div class="bg-blue-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">{{ __('admin.last_update') }}</p>
                                <p class="text-3xl font-bold">{{ $knowledgeBaseData['last_updated'] ? \Carbon\Carbon::parse($knowledgeBaseData['last_updated'])->format('d/m') : '--' }}</p>
                            </div>
                            <div class="bg-green-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100">{{ __('admin.project_status') }}</p>
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
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">{{ __('admin.trained_data') }}</h2>
                            <p class="text-gray-600 mt-1">Projenize yüklenen belgeler ve öğretilen bilgiler</p>
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
                                    <div class="text-sm text-gray-600 mb-4">
                                        <p><strong>Tür:</strong> {{ strtoupper(pathinfo($document->file_name, PATHINFO_EXTENSION)) }}</p>
                                        <p><strong>Yüklenme:</strong> {{ $document->created_at->format('d.m.Y H:i') }}</p>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <button onclick="knowledgeBaseManager.downloadDocument({{ $document->id }})" 
                                            class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                            İndir
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ml-1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                        </button>
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
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz belge yüklenmedi</h3>
                            <p class="text-gray-600 mb-4">Projenize belge yükleyerek AI modelini eğitmeye başlayın</p>

                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>



    <!-- JavaScript -->
    <script>
        class KnowledgeBaseManager {
            constructor() {
                this.init();
            }

            init() {
                this.bindEvents();
            }

            bindEvents() {
                // No modal events needed
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
                if (!confirm('Bu belgeyi silmek istediğinizden emin misiniz?')) {
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
                        this.showSuccess('Belge başarıyla silindi!');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showError(data.message || 'Belge silinemedi.');
                    }
                } catch (error) {
                    console.error('Error deleting document:', error);
                    this.showError('Belge silinirken bir hata oluştu.');
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
                        this.showError(data.message || 'Belge indirilemedi.');
                    }
                } catch (error) {
                    console.error('Error downloading document:', error);
                    this.showError('Belge indirilirken bir hata oluştu.');
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
</body>
</html> 