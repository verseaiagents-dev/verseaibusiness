@extends('dashboard.partial.admin-layout')

@section('title', 'Agent Niyetleri - ' . $agent->name)
@section('description', $agent->name . ' agent niyet yönetimi')

@section('content')
<div class="admin-content">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Agent Niyetleri</h1>
            <p class="text-gray-600">{{ $agent->name }} - Niyet Yönetimi</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.agents.show', $agent) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Geri Dön
            </a>
            <button id="addIntentBtn" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Yeni Niyet Ekle
            </button>
        </div>
    </div>

    <!-- Ana İstatistikler - Soldan Sağa -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Toplam Niyet -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Toplam Niyet</p>
                    <p class="text-2xl font-bold">{{ $intents->count() }}</p>
                </div>
                <div class="text-blue-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Aktif Niyet -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Aktif Niyet</p>
                    <p class="text-2xl font-bold">{{ $intents->where('is_active', true)->count() }}</p>
                </div>
                <div class="text-green-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Pasif Niyet -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Pasif Niyet</p>
                    <p class="text-2xl font-bold">{{ $intents->where('is_active', false)->count() }}</p>
                </div>
                <div class="text-red-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Son 7 Gün -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Son 7 Gün</p>
                    <p class="text-2xl font-bold">{{ $intents->where('created_at', '>=', now()->subDays(7))->count() }}</p>
                </div>
                <div class="text-purple-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Detay İstatistikler - Soldan Sağa -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Bu Ay -->
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-indigo-500 mb-2">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $intents->where('created_at', '>=', now()->startOfMonth())->count() }}</h3>
            <p class="text-xs text-gray-600">Bu Ay</p>
        </div>
        
        <!-- Bu Hafta -->
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-orange-500 mb-2">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $intents->where('created_at', '>=', now()->startOfWeek())->count() }}</h3>
            <p class="text-xs text-gray-600">Bu Hafta</p>
        </div>
        
        <!-- Bugün -->
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-green-500 mb-2">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $intents->where('created_at', '>=', now()->startOfDay())->count() }}</h3>
            <p class="text-xs text-gray-600">Bugün</p>
        </div>
        
        <!-- Aktiflik Oranı -->
        <div class="bg-white rounded-lg shadow-sm p-4 text-center">
            <div class="text-blue-500 mb-2">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">
                {{ $intents->count() > 0 ? round(($intents->where('is_active', true)->count() / $intents->count()) * 100, 1) : 0 }}%
            </h3>
            <p class="text-xs text-gray-600">Aktiflik Oranı</p>
        </div>
    </div>

    <!-- Performans İstatistikleri - Soldan Sağa -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- En Son Güncelleme -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">En Son Güncelleme</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $intents->count() > 0 ? $intents->sortByDesc('updated_at')->first()->updated_at->format('d.m.Y H:i') : 'Yok' }}
                    </p>
                </div>
                <div class="text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Ortalama Açıklama Uzunluğu -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Ort. Açıklama</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $intents->count() > 0 ? round($intents->avg(function($intent) { return strlen($intent->description ?? ''); }), 0) : 0 }} karakter
                    </p>
                </div>
                <div class="text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- En Uzun Niyet Adı -->
        <div class="bg-white rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">En Uzun Niyet</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $intents->count() > 0 ? $intents->sortByDesc(function($intent) { return strlen($intent->name); })->first()->name : 'Yok' }}
                    </p>
                </div>
                <div class="text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Niyet Listesi -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Niyet Listesi</h3>
        </div>
        
        <div class="p-6">
            @if($intents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Niyet Adı</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Açıklama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oluşturulma</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($intents as $intent)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $intent->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($intent->description, 100) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($intent->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Pasif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $intent->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editIntent({{ $intent->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Düzenle</button>
                                    <button onclick="toggleIntentStatus({{ $intent->id }})" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        {{ $intent->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                    </button>
                                    <button onclick="deleteIntent({{ $intent->id }})" class="text-red-600 hover:text-red-900">Sil</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    <p class="text-gray-500">Henüz niyet tanımlanmamış.</p>
                    <button onclick="showAddIntentModal()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        İlk Niyeti Ekle
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Niyet Ekleme/Düzenleme Modal -->
<div id="intentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Yeni Niyet Ekle</h3>
            </div>
            
            <form id="intentForm" class="p-6">
                <input type="hidden" id="intentId" name="intent_id">
                <input type="hidden" name="agent_id" value="{{ $agent->id }}">
                
                <div class="mb-4">
                    <label for="intentName" class="block text-sm font-medium text-gray-700 mb-2">Niyet Adı</label>
                    <input type="text" id="intentName" name="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="intentDescription" class="block text-sm font-medium text-gray-700 mb-2">Açıklama</label>
                    <textarea id="intentDescription" name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" id="intentActive" name="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                        <span class="ml-2 text-sm text-gray-700">Aktif</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeIntentModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
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
function showAddIntentModal() {
    document.getElementById('modalTitle').textContent = 'Yeni Niyet Ekle';
    document.getElementById('intentForm').reset();
    document.getElementById('intentId').value = '';
    document.getElementById('intentModal').classList.remove('hidden');
}

function editIntent(intentId) {
    // Intent verilerini getir ve modal'ı doldur
    fetch(`/admin/intents/${intentId}/edit-data`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'Niyet Düzenle';
                document.getElementById('intentId').value = data.intent.id;
                document.getElementById('intentName').value = data.intent.name;
                document.getElementById('intentDescription').value = data.intent.description || '';
                document.getElementById('intentActive').checked = data.intent.is_active;
                document.getElementById('intentModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Niyet verileri alınırken hata oluştu.');
        });
}

function closeIntentModal() {
    document.getElementById('intentModal').classList.add('hidden');
}

function toggleIntentStatus(intentId) {
    if (confirm('Niyet durumunu değiştirmek istediğinizden emin misiniz?')) {
        fetch(`/admin/intents/${intentId}/toggle-status`, {
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

function deleteIntent(intentId) {
    if (confirm('Bu niyeti silmek istediğinizden emin misiniz?')) {
        fetch(`/admin/intents/${intentId}`, {
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
                alert('Niyet silinirken hata oluştu.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Niyet silinirken hata oluştu.');
        });
    }
}

// Form submit
document.getElementById('intentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const intentId = document.getElementById('intentId').value;
    const url = intentId ? `/admin/intents/${intentId}` : '/admin/intents';
    const method = intentId ? 'PUT' : 'POST';
    
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
            closeIntentModal();
            location.reload();
        } else {
            alert('Niyet kaydedilirken hata oluştu.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Niyet kaydedilirken hata oluştu.');
    });
});

// Modal dışına tıklandığında kapat
document.getElementById('intentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeIntentModal();
    }
});

// Add Intent Button
document.getElementById('addIntentBtn').addEventListener('click', showAddIntentModal);
</script>
@endsection 