@extends('dashboard.partial.admin-layout')

@section('title', 'Agent Knowledge Base - ' . $agent->name)
@section('description', $agent->name . ' agent knowledge base yönetimi')

@section('content')
<div class="admin-content">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Agent Knowledge Base</h1>
            <p class="text-gray-600">{{ $agent->name }} - Knowledge Base Yönetimi</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.agents.show', $agent) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Geri Dön
            </a>
            <button id="addKnowledgeBtn" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Yeni İçerik Ekle
            </button>
        </div>
    </div>

    <!-- Ana İstatistikler - Soldan Sağa -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Toplam İçerik</p>
                    <p class="text-2xl font-bold">{{ $knowledgeBaseItems->count() }}</p>
                </div>
                <div class="text-blue-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Aktif İçerik</p>
                    <p class="text-2xl font-bold">{{ $knowledgeBaseItems->where('is_active', true)->count() }}</p>
                </div>
                <div class="text-green-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Pasif İçerik</p>
                    <p class="text-2xl font-bold">{{ $knowledgeBaseItems->where('is_active', false)->count() }}</p>
                </div>
                <div class="text-red-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Son 7 Gün</p>
                    <p class="text-2xl font-bold">{{ $knowledgeBaseItems->where('created_at', '>=', now()->subDays(7))->count() }}</p>
                </div>
                <div class="text-purple-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Knowledge Base Listesi -->
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Knowledge Base İçerikleri</h3>
        </div>
        
        <div class="p-6">
            @if($knowledgeBaseItems->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başlık</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İçerik</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oluşturulma</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($knowledgeBaseItems as $item)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->title }}</td>
                                <td class="px-4 py-2 text-sm text-gray-500">{{ Str::limit($item->content, 100) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    @if($item->is_active)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Pasif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">{{ $item->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editKnowledge({{ $item->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Düzenle</button>
                                    <button onclick="toggleKnowledgeStatus({{ $item->id }})" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                        {{ $item->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                                    </button>
                                    <button onclick="deleteKnowledge({{ $item->id }})" class="text-red-600 hover:text-red-900">Sil</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <p class="text-gray-500">Henüz knowledge base içeriği eklenmemiş.</p>
                    <button onclick="showAddKnowledgeModal()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                        İlk İçeriği Ekle
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Knowledge Base Ekleme/Düzenleme Modal -->
<div id="knowledgeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Yeni İçerik Ekle</h3>
            </div>
            
            <form id="knowledgeForm" class="p-6">
                <input type="hidden" id="knowledgeId" name="knowledge_id">
                <input type="hidden" name="project_id" value="{{ $agent->project_id }}">
                
                <div class="mb-4">
                    <label for="knowledgeTitle" class="block text-sm font-medium text-gray-700 mb-2">Başlık</label>
                    <input type="text" id="knowledgeTitle" name="title" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="knowledgeContent" class="block text-sm font-medium text-gray-700 mb-2">İçerik</label>
                    <textarea id="knowledgeContent" name="content" rows="8" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" id="knowledgeActive" name="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
                        <span class="ml-2 text-sm text-gray-700">Aktif</span>
                    </label>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeKnowledgeModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
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
function showAddKnowledgeModal() {
    document.getElementById('modalTitle').textContent = 'Yeni İçerik Ekle';
    document.getElementById('knowledgeForm').reset();
    document.getElementById('knowledgeId').value = '';
    document.getElementById('knowledgeModal').classList.remove('hidden');
}

function editKnowledge(knowledgeId) {
    // Knowledge verilerini getir ve modal'ı doldur
    fetch(`/admin/knowledge-base/${knowledgeId}/edit-data`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalTitle').textContent = 'İçerik Düzenle';
                document.getElementById('knowledgeId').value = data.knowledge.id;
                document.getElementById('knowledgeTitle').value = data.knowledge.title;
                document.getElementById('knowledgeContent').value = data.knowledge.content || '';
                document.getElementById('knowledgeActive').checked = data.knowledge.is_active;
                document.getElementById('knowledgeModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('İçerik verileri alınırken hata oluştu.');
        });
}

function closeKnowledgeModal() {
    document.getElementById('knowledgeModal').classList.add('hidden');
}

function toggleKnowledgeStatus(knowledgeId) {
    if (confirm('İçerik durumunu değiştirmek istediğinizden emin misiniz?')) {
        fetch(`/admin/knowledge-base/${knowledgeId}/toggle-status`, {
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

function deleteKnowledge(knowledgeId) {
    if (confirm('Bu içeriği silmek istediğinizden emin misiniz?')) {
        fetch(`/admin/knowledge-base/${knowledgeId}`, {
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
                alert('İçerik silinirken hata oluştu.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('İçerik silinirken hata oluştu.');
        });
    }
}

// Form submit
document.getElementById('knowledgeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const knowledgeId = document.getElementById('knowledgeId').value;
    const url = knowledgeId ? `/admin/knowledge-base/${knowledgeId}` : '/admin/knowledge-base';
    const method = knowledgeId ? 'PUT' : 'POST';
    
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
            closeKnowledgeModal();
            location.reload();
        } else {
            alert('İçerik kaydedilirken hata oluştu.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('İçerik kaydedilirken hata oluştu.');
    });
});

// Modal dışına tıklandığında kapat
document.getElementById('knowledgeModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeKnowledgeModal();
    }
});

// Add Knowledge Button
document.getElementById('addKnowledgeBtn').addEventListener('click', showAddKnowledgeModal);
</script>
@endsection 