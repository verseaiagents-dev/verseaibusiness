<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="light scroll-smooth group" data-layout="vertical" data-sidebar="light" data-sidebar-size="lg" data-mode="light" data-topbar="light" data-skin="default" data-navbar="sticky" data-content="fluid" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <title>VersAI - Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta content="VersAI User Profile" name="description">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('dashboard/assets/images/favicon.ico') }}">
    <script src="{{ asset('dashboard/assets/js/layout.js') }}"></script>

    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/tailwind2.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/custom-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('dashboard/assets/css/tailwind-fixes.css') }}">
    
    <style>
        .profile-card {
            transition: all 0.3s ease;
        }
        
        .profile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-card:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        
        .qr-code-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
                        <h1 class="text-3xl font-bold text-gray-900">Profil Yönetimi</h1>
                        <p class="text-gray-600 mt-1">İşletme profilinizi yönetin ve görünürlüğünüzü artırın</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('profile.edit', $profile->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Profili Düzenle
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Profile Overview -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Profile Card -->
                    <div class="lg:col-span-2">
                        <div class="profile-card bg-white rounded-xl shadow-lg p-6">
                            <div class="flex items-center space-x-4 mb-6">
                                <div class="relative">
                                    @if($profile->avatar_url)
                                        <img src="{{ $profile->avatar_url }}" alt="Profil Resmi" class="w-20 h-20 rounded-full object-cover border-4 border-gray-200">
                                    @else
                                        <div class="w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                                            {{ strtoupper(substr($profile->business_name ?? 'V', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-white"></div>
                                </div>
                                <div class="flex-1">
                                    <h2 class="text-2xl font-bold text-gray-900">{{ $profile->business_name ?? 'İşletme Adı Belirtilmemiş' }}</h2>
                                    <p class="text-gray-600">{{ $profile->industry ?? 'Sektör Belirtilmemiş' }}</p>
                                    <p class="text-sm text-gray-500">{{ $profile->location ?? 'Konum Belirtilmemiş' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $profile->is_public ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $profile->is_public ? 'Açık' : 'Gizli' }}
                                    </span>
                                </div>
                            </div>

                            @if($profile->bio)
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Hakkında</h3>
                                    <p class="text-gray-700">{{ $profile->bio }}</p>
                                </div>
                            @endif

                            <!-- Contact Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($profile->website_url)
                                    <div class="flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                                        </svg>
                                        <a href="{{ $profile->website_url }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $profile->website_url }}</a>
                                    </div>
                                @endif

                                @if($profile->contact_email)
                                    <div class="flex items-center space-x-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-gray-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                        </svg>
                                        <a href="mailto:{{ $profile->contact_email }}" class="text-blue-600 hover:text-blue-800">{{ $profile->contact_email }}</a>
                                    </div>
                                @endif
                            </div>

                            <!-- Social Links -->
                            @if($profile->social_links && count(array_filter($profile->social_links)))
                                <div class="mt-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Sosyal Medya</h3>
                                    <div class="flex space-x-4">
                                        @foreach($profile->social_links as $platform => $url)
                                            @if($url)
                                                <a href="{{ $url }}" target="_blank" class="text-gray-600 hover:text-blue-600 transition-colors">
                                                    <span class="capitalize">{{ $platform }}</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- QR Code Card -->
                    <div class="lg:col-span-1">
                        <div class="profile-card bg-white rounded-xl shadow-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">QR Kod</h3>
                            <div class="qr-code-container text-center">
                                @if($profile->share_qr_code_url)
                                    <img src="{{ $profile->share_qr_code_url }}" alt="QR Kod" class="mx-auto mb-4 w-32 h-32">
                                @else
                                    <div class="w-32 h-32 mx-auto mb-4 bg-gray-100 rounded-lg flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-12 h-12 text-gray-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5zM13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z" />
                                        </svg>
                                    </div>
                                @endif
                                <button id="generateQrBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    QR Kod Oluştur
                                </button>
                                <p class="text-sm text-gray-600 mt-2">Profilinizi paylaşmak için QR kod oluşturun</p>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Popular Topics & Reviews -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Popular Topics -->
                    <div class="profile-card bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Popüler Konular</h3>
                        @if($profile->popular_topics && count($profile->popular_topics))
                            <div class="space-y-3">
                                @foreach($profile->popular_topics as $topic)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-gray-700">{{ $topic }}</span>
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">Henüz popüler konu verisi yok</p>
                        @endif
                    </div>

                    <!-- Reviews -->
                    <div class="profile-card bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Müşteri Yorumları</h3>
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="{{ $i <= $profile->average_rating ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 {{ $i <= $profile->average_rating ? 'text-yellow-400' : 'text-gray-300' }}">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-sm text-gray-600">{{ $profile->formatted_average_rating }} ({{ $profile->reviews_count }} yorum)</span>
                            </div>
                        </div>

                        @if($profile->featured_testimonials && count($profile->featured_testimonials))
                            <div class="space-y-4">
                                @foreach($profile->featured_testimonials as $testimonial)
                                    <div class="border-l-4 border-blue-500 pl-4">
                                        <p class="text-gray-700 mb-2">"{{ $testimonial['comment'] }}"</p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900">{{ $testimonial['name'] }}</span>
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="{{ $i <= $testimonial['rating'] ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 {{ $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300' }}">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                                    </svg>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">Henüz müşteri yorumu yok</p>
                        @endif
                    </div>
                </div>

                <!-- Public Profile Link -->
                @if($profile->is_public && $profile->profile_slug)
                    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-blue-900 mb-2">Public Profil Linki</h3>
                                <p class="text-blue-700">{{ route('profile.public', $profile->profile_slug) }}</p>
                            </div>
                            <a href="{{ route('profile.public', $profile->profile_slug) }}" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Profili Görüntüle
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script src="{{ asset('dashboard/assets/js/app.js') }}"></script>
    <script>
        // QR Code Generation
        document.getElementById('generateQrBtn').addEventListener('click', function() {
            const button = this;
            const originalText = button.textContent;
            
            button.textContent = 'Oluşturuluyor...';
            button.disabled = true;
            
            fetch(`{{ route('profile.qr-code', $profile->id) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('QR kod oluşturulurken bir hata oluştu.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('QR kod oluşturulurken bir hata oluştu.');
            })
            .finally(() => {
                button.textContent = originalText;
                button.disabled = false;
            });
        });
    </script>
</body>
</html> 