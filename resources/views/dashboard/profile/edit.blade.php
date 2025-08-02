<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="light scroll-smooth group" data-layout="vertical" data-sidebar="light" data-sidebar-size="lg" data-mode="light" data-topbar="light" data-skin="default" data-navbar="sticky" data-content="fluid" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>VersAI - Profil Düzenle</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="VersAI User Profile Edit" name="description">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('dashboard/assets/images/favicon.ico') }}">
    <script src="{{ asset('dashboard/assets/js/layout.js') }}"></script>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/tailwind2.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/custom-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/tailwind-fixes.css') }}">
    
    <style>
        .form-card {
            transition: all 0.3s ease;
        }
        
        .form-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .avatar-preview {
            transition: all 0.3s ease;
        }
        
        .avatar-preview:hover {
            transform: scale(1.05);
        }
    </style>
</head>

<body class="text-base bg-body-bg text-body font-public">
    <div class="dashboard-layout">
        <!-- Mobile Toggle Button -->
        <button id="mobileToggle" class="mobile-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <!-- Sidebar -->
        @include('dashboard.partial.sidebar')

        <!-- Main Content Area -->
        <main class="dashboard-main" id="dashboardMain">
            <!-- Page Header -->
            <div class="dashboard-content">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Profil Düzenle</h1>
                        <p class="text-gray-600 mt-1">İşletme bilgilerinizi güncelleyin</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('profile.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                            Geri Dön
                        </a>
                    </div>
                </div>

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('profile.update', $userProfile->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="form-card bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Temel Bilgiler</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">İşletme Adı *</label>
                                <input type="text" id="business_name" name="business_name" value="{{ old('business_name', $userProfile->business_name) }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            </div>

                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Kullanıcı Adı</label>
                                <div class="relative">
                                    <input type="text" id="username" name="username" value="{{ old('username', $userProfile->username) }}" 
                                           class="w-full px-3 py-2 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <button type="button" id="checkUsernameBtn" class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1 text-gray-400 hover:text-blue-600 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div id="usernameValidationMessage" class="text-xs mt-1 hidden"></div>
                                <p class="text-xs text-gray-500 mt-1">Profil URL'sinde kullanılacak benzersiz kullanıcı adı</p>
                            </div>

                            <div>
                                <label for="industry" class="block text-sm font-medium text-gray-700 mb-2">Sektör</label>
                                <select id="industry" name="industry" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Sektör Seçin</option>
                                    <option value="e-ticaret" {{ old('industry', $userProfile->industry) == 'e-ticaret' ? 'selected' : '' }}>E-Ticaret</option>
                                    <option value="turizm" {{ old('industry', $userProfile->industry) == 'turizm' ? 'selected' : '' }}>Turizm</option>
                                    <option value="emlak" {{ old('industry', $userProfile->industry) == 'emlak' ? 'selected' : '' }}>Emlak</option>
                                    <option value="restoran" {{ old('industry', $userProfile->industry) == 'restoran' ? 'selected' : '' }}>Restoran</option>
                                    <option value="eğitim" {{ old('industry', $userProfile->industry) == 'eğitim' ? 'selected' : '' }}>Eğitim</option>
                                    <option value="sağlık" {{ old('industry', $userProfile->industry) == 'sağlık' ? 'selected' : '' }}>Sağlık</option>
                                    <option value="teknoloji" {{ old('industry', $userProfile->industry) == 'teknoloji' ? 'selected' : '' }}>Teknoloji</option>
                                    <option value="diğer" {{ old('industry', $userProfile->industry) == 'diğer' ? 'selected' : '' }}>Diğer</option>
                                </select>
                            </div>

                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Konum</label>
                                <input type="text" id="location" name="location" value="{{ old('location', $userProfile->location) }}" 
                                       placeholder="Şehir, Ülke" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Hakkında</label>
                            <textarea id="bio" name="bio" rows="4" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="İşletmeniz hakkında kısa bir açıklama yazın...">{{ old('bio', $userProfile->bio) }}</textarea>
                        </div>
                    </div>

                    <!-- Avatar Upload -->
                    <div class="form-card bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Profil Resmi</h2>
                        
                        <div class="flex items-center space-x-6">
                            <div class="avatar-preview">
                                @if($userProfile->avatar_url)
                                    <img src="{{ $userProfile->avatar_url }}" alt="Mevcut Avatar" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                                @else
                                    <div class="w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                                        {{ strtoupper(substr($userProfile->business_name ?? 'V', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex-1">
                                <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">Yeni Resim Yükle</label>
                                <input type="file" id="avatar" name="avatar" accept="image/*" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, GIF formatları desteklenir. Maksimum 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="form-card bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">İletişim Bilgileri</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="website_url" class="block text-sm font-medium text-gray-700 mb-2">Web Sitesi</label>
                                <input type="url" id="website_url" name="website_url" value="{{ old('website_url', $userProfile->website_url) }}" 
                                       placeholder="https://example.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">İletişim E-postası</label>
                                <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $userProfile->contact_email) }}" 
                                       placeholder="contact@example.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Links -->
                    <div class="form-card bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Sosyal Medya</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="social_links_instagram" class="block text-sm font-medium text-gray-700 mb-2">Instagram</label>
                                <input type="url" id="social_links_instagram" name="social_links[instagram]" 
                                       value="{{ old('social_links.instagram', $userProfile->social_links['instagram'] ?? '') }}" 
                                       placeholder="https://instagram.com/username" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="social_links_linkedin" class="block text-sm font-medium text-gray-700 mb-2">LinkedIn</label>
                                <input type="url" id="social_links_linkedin" name="social_links[linkedin]" 
                                       value="{{ old('social_links.linkedin', $userProfile->social_links['linkedin'] ?? '') }}" 
                                       placeholder="https://linkedin.com/company/company" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="social_links_facebook" class="block text-sm font-medium text-gray-700 mb-2">Facebook</label>
                                <input type="url" id="social_links_facebook" name="social_links[facebook]" 
                                       value="{{ old('social_links.facebook', $userProfile->social_links['facebook'] ?? '') }}" 
                                       placeholder="https://facebook.com/page" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="social_links_twitter" class="block text-sm font-medium text-gray-700 mb-2">Twitter</label>
                                <input type="url" id="social_links_twitter" name="social_links[twitter]" 
                                       value="{{ old('social_links.twitter', $userProfile->social_links['twitter'] ?? '') }}" 
                                       placeholder="https://twitter.com/username" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Privacy Settings -->
                    <div class="form-card bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Gizlilik Ayarları</h2>
                        
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" id="is_public" name="is_public" value="1" 
                                   {{ old('is_public', $userProfile->is_public) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                            <label for="is_public" class="text-sm font-medium text-gray-700">Profilimi herkese açık yap</label>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Bu seçenek işaretliyse, profiliniz public link ile paylaşılabilir.</p>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-between pt-6">
                        <a href="{{ route('profile.index') }}" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            İptal
                        </a>
                        
                        <div class="flex space-x-4">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Değişiklikleri Kaydet
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script src="{{ asset('dashboard/assets/js/app.js') }}"></script>
    <script>
        // Avatar preview
        document.getElementById('avatar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.avatar-preview');
                    preview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview" class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // Username validation
        let usernameValidationTimeout;
        const usernameInput = document.getElementById('username');
        const checkUsernameBtn = document.getElementById('checkUsernameBtn');
        const usernameValidationMessage = document.getElementById('usernameValidationMessage');
        const submitBtn = document.querySelector('button[type="submit"]');

        // Check username availability
        function checkUsername() {
            const username = usernameInput.value.trim();
            
            if (!username) {
                hideValidationMessage();
                return;
            }

            // Show loading state
            checkUsernameBtn.innerHTML = `
                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            `;
            checkUsernameBtn.classList.add('text-blue-600');

            fetch('{{ route("profile.check-username") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    username: username,
                    current_user_id: {{ auth()->id() }}
                })
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                checkUsernameBtn.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                `;

                if (data.available) {
                    showValidationMessage(data.message, 'success');
                    usernameInput.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
                    usernameInput.classList.add('border-green-500', 'focus:ring-green-500', 'focus:border-green-500');
                    submitBtn.disabled = false;
                } else {
                    showValidationMessage(data.message, 'error');
                    usernameInput.classList.remove('border-green-500', 'focus:ring-green-500', 'focus:border-green-500');
                    usernameInput.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
                    submitBtn.disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                checkUsernameBtn.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                `;
                showValidationMessage('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
            });
        }

        function showValidationMessage(message, type) {
            usernameValidationMessage.textContent = message;
            usernameValidationMessage.className = `text-xs mt-1 ${type === 'success' ? 'text-green-600' : 'text-red-600'}`;
            usernameValidationMessage.classList.remove('hidden');
        }

        function hideValidationMessage() {
            usernameValidationMessage.classList.add('hidden');
            usernameInput.classList.remove('border-red-500', 'border-green-500', 'focus:ring-red-500', 'focus:ring-green-500', 'focus:border-red-500', 'focus:border-green-500');
            submitBtn.disabled = false;
        }

        // Event listeners
        checkUsernameBtn.addEventListener('click', checkUsername);

        usernameInput.addEventListener('input', function() {
            clearTimeout(usernameValidationTimeout);
            hideValidationMessage();
            
            // Debounce validation
            usernameValidationTimeout = setTimeout(() => {
                if (this.value.trim()) {
                    checkUsername();
                }
            }, 500);
        });

        // Form submission prevention if username is invalid
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = usernameInput.value.trim();
            if (username && usernameValidationMessage.classList.contains('text-red-600')) {
                e.preventDefault();
                alert('Lütfen geçerli bir kullanıcı adı seçin.');
            }
        });
    </script>
</body>
</html> 