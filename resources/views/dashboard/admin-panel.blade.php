@extends('dashboard.partial.admin-layout')

@section('title', 'VersAI Admin Panel')
@section('description', 'VersAI Admin Panel')

@section('additional-styles')
<style>
    .modal-overlay {
        backdrop-filter: blur(4px);
    }
    
    .modal-content {
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .admin-card {
        transition: all 0.2s ease-in-out;
    }
    
    .admin-card:hover {
        transform: translateY(-2px);
    }
    
    .admin-content {
        margin-bottom: 2rem;
    }
    
    .admin-content:last-child {
        margin-bottom: 0;
    }

    .admin-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .admin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
</style>
@endsection

@section('content')
            <!-- Page Header -->
            <div class="admin-content">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Admin Panel</h1>
                        <p class="text-gray-600 mt-1">VersAI yönetim paneli</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Admin Badge -->
                        <span class="admin-badge">ADMIN</span>
                        
                        <!-- Switch to User Panel Button -->
                        <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            Kullanıcı Paneli
                        </a>
                    </div>
                </div>

                <!-- Admin Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-purple-100">Toplam Kullanıcı</p>
                                <p class="text-3xl font-bold">--</p>
                            </div>
                            <div class="bg-purple-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100">Aktif Agent</p>
                                <p class="text-3xl font-bold">--</p>
                            </div>
                            <div class="bg-green-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423L16.5 15.75l.394 1.183a2.25 2.25 0 001.423 1.423L19.5 18.75l-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100">Toplam Proje</p>
                                <p class="text-3xl font-bold">--</p>
                            </div>
                            <div class="bg-blue-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100">Sistem Durumu</p>
                                <p class="text-3xl font-bold">Aktif</p>
                            </div>
                            <div class="bg-red-400 rounded-lg p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Sections -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- User Management -->
                    <div class="admin-card bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Kullanıcı Yönetimi</h3>
                            <button class="bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                Yeni Ekle
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                        U
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Kullanıcılar</p>
                                        <p class="text-sm text-gray-500">Toplam: --</p>
                                    </div>
                                </div>
                                <span class="text-green-600 text-sm font-medium">Aktif</span>
                            </div>
                        </div>
                    </div>

                    <!-- System Settings -->
                    <div class="admin-card bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Sistem Ayarları</h3>
                            <button class="bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-700 transition-colors text-sm">
                                Düzenle
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-gray-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.591 1.064c1.513-.947 3.43.97 2.483 2.483a1.724 1.724 0 001.064 2.591c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.064 2.591c.947 1.513-.97 3.43-2.483 2.483a1.724 1.724 0 00-2.591 1.064c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.591-1.064c-1.513.947-3.43-.97-2.483-2.483a1.724 1.724 0 00-1.064-2.591c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.064-2.591c-.947-1.513.97-3.43 2.483-2.483 1.512.947 3.43-.97 2.483-2.483z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">Genel Ayarlar</p>
                                        <p class="text-sm text-gray-500">Sistem konfigürasyonu</p>
                                    </div>
                                </div>
                                <span class="text-blue-600 text-sm font-medium">Yapılandırıldı</span>
                            </div>
                        </div>
                    </div>

                    <!-- Logs -->
                    <div class="admin-card bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Sistem Logları</h3>
                            <button class="bg-orange-600 text-white px-3 py-1 rounded-lg hover:bg-orange-700 transition-colors text-sm">
                                Görüntüle
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-gray-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">Log Dosyaları</p>
                                        <p class="text-sm text-gray-500">Son güncelleme: --</p>
                                    </div>
                                </div>
                                <span class="text-green-600 text-sm font-medium">Temiz</span>
                            </div>
                        </div>
                    </div>

                    <!-- Backup -->
                    <div class="admin-card bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900">Yedekleme</h3>
                            <button class="bg-purple-600 text-white px-3 py-1 rounded-lg hover:bg-purple-700 transition-colors text-sm">
                                Yedekle
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 text-gray-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900">Veritabanı</p>
                                        <p class="text-sm text-gray-500">Son yedekleme: --</p>
                                    </div>
                                </div>
                                <span class="text-blue-600 text-sm font-medium">Güncel</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
@endsection 