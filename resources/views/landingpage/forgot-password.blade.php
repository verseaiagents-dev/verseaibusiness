<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="dark scroll-smooth" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        @include('landingpage.partials.headcontent', [
            'title' => 'VersAI - Forgot Password',
            'description' => 'VersAI - Reset your password and regain access to your account',
            'keywords' => 'VersAI, AI Agent, Password Reset, Forgot Password, Account Recovery'
        ])
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    </head>
    
    <body class="font-figtree text-base text-slate-900 dark:text-white dark:bg-slate-900">
        <!-- Loader Start -->
        <!-- <div id="preloader">
            <div id="status">
                <div class="logo">
                    <img src="{{ asset('landingpage/images/logo-icon-64.png') }}" class="d-block mx-auto animate-[spin_10s_linear_infinite]" alt="">
                </div>
                <div class="justify-content-center">
                    <div class="text-center">
                        <h4 class="mb-0 mt-2 text-lg font-semibold">VersAI</h4>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Loader End -->
        
        <!-- Start Navbar -->
        <nav id="topnav" class="defaultscroll is-sticky fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-gray-200 dark:border-slate-800">
            <div class="container">
                <!-- Logo container-->
                <a class="logo" href="{{ route('home') }}">
                    <img src="{{ asset('landingpage/images/logo-dark.png') }}" class="h-6 inline-block dark:hidden" alt="">
                    <img src="{{ asset('landingpage/images/logo-white.png') }}" class="h-6 hidden dark:inline-block" alt="">
                </a>
                <!-- End Logo container-->

                <!-- Start Mobile Toggle -->
                <div class="menu-extras">
                    <div class="menu-item">
                        <a class="navbar-toggle" id="isToggle" onclick="toggleMenu()">
                            <div class="lines">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </a>
                    </div>
                </div>
                <!-- End Mobile Toggle -->

                <!--Login button Start-->
                <ul class="buy-button list-none mb-0">
                    <li class="inline mb-0">
                        <a href="{{ route('login') }}">
                            <span class="py-[6px] px-4 md:inline hidden items-center justify-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400/5 hover:bg-amber-400 border border-amber-400/10 hover:border-amber-400 text-amber-400 hover:text-white font-semibold">{{ __('landing.auth_sign_in') }}</span>
                            <span class="py-[6px] px-4 inline md:hidden items-center justify-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400 hover:bg-amber-500 border border-amber-400 hover:border-amber-500 text-white font-semibold">{{ __('landing.auth_sign_in') }}</span>
                        </a>
                    </li>
            
                    <li class="md:inline hidden ps-1 mb-0 ">
                        <a href="{{ route('signup') }}" target="_blank" class="py-[6px] px-4 inline-block items-center justify-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400 hover:bg-amber-500 border border-amber-400 hover:border-amber-500 text-white font-semibold">{{ __('landing.auth_sign_up') }}</a>
                    </li>
                </ul>
                <!--Login button End-->
            </div><!--end container-->
        </nav><!--end header-->
        <!-- End Navbar -->

        <!-- Start Hero -->
        <section class="relative overflow-hidden h-screen flex items-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
            <div class="absolute inset-0 bg-slate-950/20"></div>
            <div class="container relative">
                <div class="md:flex justify-end">
                    <div class="lg:w-1/3 md:w-2/4">
                        <div class="rounded shadow bg-white dark:bg-slate-900 p-6">
                            <img src="{{ asset('landingpage/images/logo-icon-64.png') }}" alt="VersAI Logo">

                            <h5 class="mt-6 text-xl font-semibold">Forgot password</h5>

                            <p class="text-slate-400 mt-2">Please enter your email address. You will receive a link to create a new password via email.</p>

                            <!-- Success Message -->
                            @if(session('success'))
                                <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <!-- Error Messages -->
                            @if($errors->any())
                                <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                    <ul class="list-disc list-inside">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form class="text-start mt-4" method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm">
                                @csrf
                                <div class="grid grid-cols-1">
                                    <div class="mb-4">
                                        <label class="font-semibold" for="LoginEmail">Email Address:</label>
                                        <input id="LoginEmail" name="email" type="email" class="form-input mt-3 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-200 focus:border-amber-400 dark:border-gray-800 dark:focus:border-amber-400 focus:ring-0 @error('email') border-red-500 @enderror" placeholder="name@example.com" value="{{ old('email') }}" required>
                                        @error('email')
                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
    
                                    <div class="mb-4">
                                        <button type="submit" class="py-2 px-5 inline-block font-semibold tracking-wide border align-middle duration-500 text-base text-center bg-amber-400 hover:bg-amber-500 border-amber-400 hover:border-amber-500 text-white rounded-md w-full" id="submitBtn">
                                            <span class="submit-text">Send Reset Link</span>
                                            <span class="loading-text hidden">Sending...</span>
                                        </button>
                                    </div>

                                    <div class="mb-4">
                                        <a href="#" class="py-2 px-5 inline-block font-semibold tracking-wide border align-middle duration-500 text-base text-center bg-gray-800/5 hover:bg-gray-800 border-gray-800/10 hover:border-gray-800 text-gray-800 dark:text-white hover:text-white rounded-md w-full"><i class="mdi mdi-google"></i> Sign in with Google</a>
                                    </div>
    
                                    <div class="text-center">
                                        <span class="text-slate-400 me-2">Remember your password ? </span> <a href="{{ route('login') }}" class="text-slate-900 dark:text-white font-bold inline-block">Sign in</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!--end grid-->
            </div>
        </section>
        <!-- End Hero -->

        <!-- JAVASCRIPTS -->
        <script src="{{ asset('landingpage/libs/feather-icons/feather.min.js') }}"></script>
        <script src="{{ asset('landingpage/js/plugins.init.js') }}"></script>
        <script src="{{ asset('landingpage/js/app.js') }}"></script>
        <script>
            if(window.feather) { window.feather.replace(); }

            // AJAX Forgot Password Form
            document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const form = this;
                const submitBtn = document.getElementById('submitBtn');
                const submitText = submitBtn.querySelector('.submit-text');
                const loadingText = submitBtn.querySelector('.loading-text');
                
                // Show loading state
                submitBtn.disabled = true;
                submitText.classList.add('hidden');
                loadingText.classList.remove('hidden');
                
                // Get form data
                const formData = new FormData(form);
                
                // Send AJAX request
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showMessage('success', data.message);
                        
                        // Reset form
                        form.reset();
                    } else {
                        // Show error message
                        showMessage('error', data.message || 'Password reset request failed. Please try again.');
                    }
                    
                    // Reset button state
                    submitBtn.disabled = false;
                    submitText.classList.remove('hidden');
                    loadingText.classList.add('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('error', 'An error occurred. Please try again.');
                    
                    // Reset button state
                    submitBtn.disabled = false;
                    submitText.classList.remove('hidden');
                    loadingText.classList.add('hidden');
                });
            });
            
            // Function to show messages
            function showMessage(type, message) {
                const messageDiv = document.createElement('div');
                messageDiv.className = `mt-4 p-4 rounded ${type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'}`;
                messageDiv.textContent = message;
                
                const form = document.getElementById('forgotPasswordForm');
                form.parentNode.insertBefore(messageDiv, form);
                
                // Remove message after 5 seconds
                setTimeout(() => {
                    messageDiv.remove();
                }, 5000);
            }
        </script>
        <!-- JAVASCRIPTS -->
    </body>
</html> 