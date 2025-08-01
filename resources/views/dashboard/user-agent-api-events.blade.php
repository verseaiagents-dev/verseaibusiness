@extends('dashboard.partial.user-layout')

@section('title', $agent->name . ' - API Event Yönetimi')
@section('description', 'AI Agent API Event Yönetimi')

@section('content')
<div class="admin-content">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center space-x-2 mb-2">
                <a href="{{ route('user.api-events.index') }}" class="text-blue-500 hover:text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ $agent->name }}</h1>
                <span class="px-3 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                    {{ $agent->sector == 'ecommerce' ? 'E-Ticaret' : ($agent->sector == 'real_estate' ? 'Emlak' : ($agent->sector == 'hotel' ? 'Otel' : ucfirst($agent->sector))) }}
                </span>
            </div>
            <p class="text-gray-600">{{ $agent->sector_name }} - API Event Yönetimi</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('user.intents.show', $agent) }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                Niyet Yönet
            </a>
            <button id="addApiEventBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors">
                Yeni API Event Ekle
            </button>
        </div>
    </div>

    <!-- İstatistikler - Yatay Görünüm -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        @php
            $totalApiEvents = $apiEvents->count();
            $activeApiEvents = $apiEvents->where('is_active', true)->count();
            $getEvents = $apiEvents->where('http_method', 'GET')->count();
            $postEvents = $apiEvents->where('http_method', 'POST')->count();
        @endphp
        
        <!-- Desktop Yatay Görünüm -->
        <div class="hidden md:flex items-center justify-between">
            <!-- Toplam API Event -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Toplam API Event</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalApiEvents }}</p>
                </div>
            </div>

            <!-- Ayırıcı -->
            <div class="w-px h-16 bg-gray-200"></div>

            <!-- Aktif Event -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Aktif Event</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $activeApiEvents }}</p>
                </div>
            </div>

            <!-- Ayırıcı -->
            <div class="w-px h-16 bg-gray-200"></div>

            <!-- GET Event -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">GET Event</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $getEvents }}</p>
                </div>
            </div>

            <!-- Ayırıcı -->
            <div class="w-px h-16 bg-gray-200"></div>

            <!-- POST Event -->
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">POST Event</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $postEvents }}</p>
                </div>
            </div>
        </div>

        <!-- Mobil Grid Görünüm -->
        <div class="md:hidden grid grid-cols-2 gap-4">
            <!-- Toplam API Event -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">Toplam API Event</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalApiEvents }}</p>
                </div>
            </div>

            <!-- Aktif Event -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">Aktif Event</p>
                    <p class="text-xl font-bold text-gray-900">{{ $activeApiEvents }}</p>
                </div>
            </div>

            <!-- GET Event -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">GET Event</p>
                    <p class="text-xl font-bold text-gray-900">{{ $getEvents }}</p>
                </div>
            </div>

            <!-- POST Event -->
            <div class="flex items-center space-x-3 p-4 bg-gray-50 rounded-lg">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-600">POST Event</p>
                    <p class="text-xl font-bold text-gray-900">{{ $postEvents }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- API Event'ler Listesi -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">API Event'ler</h2>
        </div>
        
        <div class="p-6">
            <div class="space-y-4">
                @foreach($apiEvents as $apiEvent)
                <div class="border border-gray-200 rounded-lg p-4" data-api-event-id="{{ $apiEvent->id }}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <h3 class="text-lg font-medium text-gray-900">{{ $apiEvent->name }}</h3>
                                <span class="px-2 py-1 text-xs rounded {{ $apiEvent->http_method === 'GET' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $apiEvent->http_method }}
                                </span>
                                @if($apiEvent->intent)
                                <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">
                                    {{ ucfirst(str_replace('_', ' ', $apiEvent->intent->name)) }}
                                </span>
                                @endif
                            </div>
                            
                            @if($apiEvent->description)
                            <p class="text-sm text-gray-600 mb-3">{{ $apiEvent->description }}</p>
                            @endif
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="api-event-toggle rounded text-blue-600" 
                                       data-api-event-id="{{ $apiEvent->id }}"
                                       {{ $apiEvent->is_active ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Aktif</span>
                            </label>
                            
                            <button class="test-api-event text-yellow-500 hover:text-yellow-700" 
                                    data-api-event-id="{{ $apiEvent->id }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </button>
                            
                            <button class="edit-api-event text-blue-500 hover:text-blue-700" 
                                    data-api-event-id="{{ $apiEvent->id }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            
                            <button class="delete-api-event text-red-500 hover:text-red-700" 
                                    data-api-event-id="{{ $apiEvent->id }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- API Event Detayları -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Endpoint URL</h4>
                            <p class="text-sm text-gray-600 bg-gray-50 p-2 rounded">{{ $apiEvent->endpoint_url }}</p>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Headers</h4>
                            @if($apiEvent->headers && count($apiEvent->headers) > 0)
                                <div class="space-y-1">
                                    @foreach($apiEvent->headers as $key => $value)
                                    <div class="text-xs text-gray-600">
                                        <span class="font-medium">{{ $key }}:</span> {{ $value }}
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-500">Tanımlanmamış</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($apiEvent->body_template && count($apiEvent->body_template) > 0)
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Body Template</h4>
                        <div class="bg-gray-50 p-3 rounded">
                            <pre class="text-xs text-gray-600">{{ json_encode($apiEvent->body_template, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
                
                @if($apiEvents->isEmpty())
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Henüz API Event Tanımlanmamış</h3>
                    <p class="text-gray-600">Bu agent için API event tanımlayarak başlayın.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Yeni API Event Ekleme Modal -->
<div id="apiEventModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-1/2 max-w-2xl mx-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Yeni API Event Ekle</h2>
            <button id="closeModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="apiEventForm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Adı *</label>
                    <input type="text" name="name" required 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Örn: ürün_ekle_event">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HTTP Metodu *</label>
                    <select name="http_method" required 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Seçin</option>
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="DELETE">DELETE</option>
                        <option value="PATCH">PATCH</option>
                    </select>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Endpoint URL *</label>
                <input type="url" name="endpoint_url" required 
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       placeholder="https://api.example.com/endpoint">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama</label>
                <textarea name="description" rows="3" 
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Bu API event'in ne yaptığını açıklayın..."></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bağlı Niyet (Opsiyonel)</label>
                <select name="intent_id" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Seçin</option>
                    @foreach($intents as $intent)
                    <option value="{{ $intent->id }}">{{ ucfirst(str_replace('_', ' ', $intent->name)) }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" id="cancelBtn" class="px-4 py-2 text-gray-600 hover:text-gray-800">İptal</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">API Event Oluştur</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal kontrolü
    const modal = document.getElementById('apiEventModal');
    const addBtn = document.getElementById('addApiEventBtn');
    const closeBtn = document.getElementById('closeModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const form = document.getElementById('apiEventForm');

    addBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));

    // Modal dışına tıklama ile kapatma
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });

    // Form gönderimi
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const formData = new FormData(this);
            const response = await fetch(`/user/api-events/{{ $agent->id }}`, {
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

    // API Event toggle
    document.querySelectorAll('.api-event-toggle').forEach(toggle => {
        toggle.addEventListener('change', async function() {
            const apiEventId = this.dataset.apiEventId;
            const isActive = this.checked;
            
            try {
                const response = await fetch(`/user/api-events/${apiEventId}`, {
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

    // API Event test
    document.querySelectorAll('.test-api-event').forEach(btn => {
        btn.addEventListener('click', async function() {
            const apiEventId = this.dataset.apiEventId;
            
            try {
                const response = await fetch(`/user/api-events/${apiEventId}/test`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    showNotification(result.message, 'success');
                } else {
                    const error = await response.json();
                    showNotification(error.message || 'Test başarısız', 'error');
                }
            } catch (error) {
                showNotification('Test sırasında hata oluştu', 'error');
            }
        });
    });

    // API Event silme
    document.querySelectorAll('.delete-api-event').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Bu API event\'i silmek istediğinizden emin misiniz?')) {
                return;
            }
            
            const apiEventId = this.dataset.apiEventId;
            
            try {
                const response = await fetch(`/user/api-events/${apiEventId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    showNotification(result.message, 'success');
                    document.querySelector(`[data-api-event-id="${apiEventId}"]`).remove();
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
@endpush 