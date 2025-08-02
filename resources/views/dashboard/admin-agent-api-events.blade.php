@extends('dashboard.partial.admin-layout')

@section('title', 'Agent API Events - ' . $agent->name)
@section('description', $agent->name . ' agent API events yönetimi')

@section('content')
<div class="admin-content">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Agent API Events</h1>
            <p class="text-gray-600">{{ $agent->name }} - API Events Yönetimi</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.agents.show', $agent) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Geri Dön
            </a>
            <button id="addApiEventBtn" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Yeni API Event Ekle
            </button>
        </div>
    </div>

    <!-- İstatistikler -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <div class="text-red-500 mb-3">
                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $apiEvents->count() }}</h3>
            <p class="text-sm text-gray-600">Toplam API Event</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <div class="text-green-500 mb-3">
                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $apiEvents->where('is_active', true)->count() }}</h3>
            <p class="text-sm text-gray-600">Aktif Event</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <div class="text-red-500 mb-3">
                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $apiEvents->where('is_active', false)->count() }}</h3>
            <p class="text-sm text-gray-600">Pasif Event</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 text-center">
            <div class="text-blue-500 mb-3">
                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ $apiEvents->where('created_at', '>=', now()->subDays(7))->count() }}</h3>
            <p class="text-sm text-gray-600">Son 7 Gün</p>
        </div>
    </div>

    <!-- API Events Listesi -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">API Events Listesi</h3>
        </div>
        
        <div class="p-6">
            @if($apiEvents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Adı</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endpoint</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Niyet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oluşturulma</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($apiEvents as $event)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $event->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $event->endpoint }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $event->method }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $event->intent->name ?? 'Niyet Yok' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($event->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Pasif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $event->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editApiEvent({{ $event->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Düzenle</button>
                                    <button onclick="toggleApiEventStatus({{ $event->id }})" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        {{ $event->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                    </button>
                                    <button onclick="deleteApiEvent({{ $event->id }})" class="text-red-600 hover:text-red-900">Sil</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <p class="text-gray-500">Henüz API event tanımlanmamış.</p>
                    <button onclick="showAddApiEventModal()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        İlk API Event'i Ekle
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- API Event Ekleme/Düzenleme Modal -->
<div id="apiEventModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Yeni API Event Ekle</h3>
            </div>
            
            <form id="apiEventForm" class="p-6">
                <input type="hidden" id="apiEventId" name="api_event_id">
                <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                
                <div class="mb-4">
                    <label for="apiEventName" class="block text-sm font-medium text-gray-700 mb-2">Event Adı</label>
                    <input type="text" id="apiEventName" name="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="apiEventEndpoint" class="block text-sm font-medium text-gray-700 mb-2">Endpoint</label>
                    <input type="text" id="apiEventEndpoint" name="endpoint" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="apiEventMethod" class="block text-sm font-medium text-gray-700 mb-2">HTTP Method</label>
                    <select id="apiEventMethod" name="method" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="DELETE">DELETE</option>
                        <option value="PATCH">PATCH</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="apiEventIntent" class="block text-sm font-medium text-gray-700 mb-2">Bağlı Niyet</label>
                    <select id="apiEventIntent" name="intent_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Niyet Seçin</option>
                        @foreach($agent->intents as $intent)
                            <option value="{{ $intent->id }}">{{ $intent->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" id="apiEventActive" name="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                        <span class="ml-2 text-sm text-gray-700">Aktif</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeApiEventModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                        İptal
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddApiEventModal() {
    document.getElementById('modalTitle').textContent = 'Yeni API Event Ekle';
    document.getElementById('apiEventForm').reset();
    document.getElementById('apiEventId').value = '';
    document.getElementById('apiEventModal').classList.remove('hidden');
}

function editApiEvent(apiEventId) {
    // API Event verilerini getir ve modal'ı doldur
    fetch(`/admin/api-events/${apiEventId}/edit-data`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'API Event Düzenle';
                document.getElementById('apiEventId').value = data.apiEvent.id;
                document.getElementById('apiEventName').value = data.apiEvent.name;
                document.getElementById('apiEventEndpoint').value = data.apiEvent.endpoint;
                document.getElementById('apiEventMethod').value = data.apiEvent.method;
                document.getElementById('apiEventIntent').value = data.apiEvent.intent_id || '';
                document.getElementById('apiEventActive').checked = data.apiEvent.is_active;
                document.getElementById('apiEventModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('API Event verileri alınırken hata oluştu.');
        });
}

function closeApiEventModal() {
    document.getElementById('apiEventModal').classList.add('hidden');
}

function toggleApiEventStatus(apiEventId) {
    if (confirm('API Event durumunu değiştirmek istediğinizden emin misiniz?')) {
        fetch(`/admin/api-events/${apiEventId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Durum güncellenirken hata oluştu.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Durum güncellenirken hata oluştu.');
        });
    }
}

function deleteApiEvent(apiEventId) {
    if (confirm('Bu API Event\'i silmek istediğinizden emin misiniz?')) {
        fetch(`/admin/api-events/${apiEventId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('API Event silinirken hata oluştu.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('API Event silinirken hata oluştu.');
        });
    }
}

// Form submit
document.getElementById('apiEventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const apiEventId = document.getElementById('apiEventId').value;
    const url = apiEventId ? `/admin/api-events/${apiEventId}` : '/admin/api-events';
    const method = apiEventId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeApiEventModal();
            location.reload();
        } else {
            alert('API Event kaydedilirken hata oluştu.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('API Event kaydedilirken hata oluştu.');
    });
});

// Modal dışına tıklandığında kapat
document.getElementById('apiEventModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApiEventModal();
    }
});

// Add API Event Button
document.getElementById('addApiEventBtn').addEventListener('click', showAddApiEventModal);
</script>
@endsection 