<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark scroll-smooth" dir="ltr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('landing.app_name') }} - {{ __('landing.app_description') }}</title>
        <meta name="description" content="{{ __('landing.app_description') }}">
        <meta name="keywords" content="AI, Copywriting, Tailwind, Responsive, Artificial Intelligence, Robot, Robot AI">
        <meta name="author" content="Shreethemes">
        <meta name="website" content="https://shreethemes.in">
        <meta name="email" content="support@shreethemes.in">
        <meta name="version" content="1.0">
        <!-- favicon -->
        <link href="assets/images/favicon.ico" rel="shortcut icon">

        <!-- Css -->
        <!-- Main Css -->
        <link href="assets/libs/@mdi/font/css/materialdesignicons.min.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="assets/css/tailwind.css">

    </head>
    
    <body class="font-figtree text-base text-slate-900 dark:text-white dark:bg-slate-900">
        <!-- Loader Start -->
        <!-- <div id="preloader">
            <div id="status">
                <div class="logo">
                    <img src="assets/images/logo-icon-64.png" class="d-block mx-auto animate-[spin_10s_linear_infinite]" alt="">
                </div>
                <div class="justify-content-center">
                    <div class="text-center">
                        <h4 class="mb-0 mt-2 text-lg font-semibold">{{ __('landing.app_name') }}</h4>
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
                            <img src="assets/images/logo-icon-64.png" alt="{{ __('landing.app_name') }}">

                            <h5 class="mt-6 text-xl font-semibold">{{ __('landing.auth_login_subtitle') }}</h5>

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

                            <form class="text-start mt-4" method="POST" action="{{ route('login.post') }}">
                                @csrf
                                <div class="grid grid-cols-1">
                                    <div class="mb-4">
                                        <label class="font-semibold" for="LoginEmail">{{ __('landing.auth_email') }}:</label>
                                        <input id="LoginEmail" name="email" type="email" class="form-input mt-3 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-200 focus:border-amber-400 dark:border-gray-800 dark:focus:border-amber-400 focus:ring-0 @error('email') border-red-500 @enderror" placeholder="{{ __('landing.auth_email') }}" value="{{ old('email') }}" required>
                                        @error('email')
                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
    
                                    <div class="mb-4">
                                        <label class="font-semibold" for="LoginPassword">{{ __('landing.auth_password') }}:</label>
                                        <input id="LoginPassword" name="password" type="password" class="form-input mt-3 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-200 focus:border-amber-400 dark:border-gray-800 dark:focus:border-amber-400 focus:ring-0 @error('password') border-red-500 @enderror" placeholder="{{ __('landing.auth_password') }}" required>
                                        @error('password')
                                            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
    
                                    <div class="flex justify-between mb-4">
                                        <div class="flex items-center mb-0">
                                            <input class="form-checkbox rounded border-gray-200 dark:border-gray-800 text-amber-400 focus:border-amber-300 focus:ring focus:ring-offset-0 focus:ring-amber-200 focus:ring-opacity-50 me-2" type="checkbox" value="1" id="RememberMe" name="remember">
                                            <label class="form-checkbox-label text-slate-400" for="RememberMe">{{ __('landing.auth_remember') }}</label>
                                        </div>
                                        <p class="text-slate-400 mb-0"><a href="{{ route('password.request') }}" class="text-slate-400">{{ __('landing.auth_forgot_password') }}</a></p>
                                    </div>
    
                                    <div class="mb-4">
                                        <button type="submit" class="py-2 px-5 inline-block tracking-wide border align-middle duration-500 text-base text-center bg-amber-400 hover:bg-amber-500 border-amber-400 hover:border-amber-500 text-white rounded-md w-full">{{ __('landing.auth_sign_in') }}</button>
                                    </div>

                                    <div class="mb-4">
                                        <a href="" class="py-2 px-5 inline-block font-semibold tracking-wide border align-middle duration-500 text-base text-center bg-gray-800/5 hover:bg-gray-800 border-gray-800/10 hover:border-gray-800 text-gray-800 dark:text-white hover:text-white rounded-md w-full"><i class="mdi mdi-google"></i> {{ __('landing.auth_sign_in_with_google') }}</a>
                                    </div>
    
                                    <div class="text-center">
                                        <span class="text-slate-400 me-2">{{ __('landing.auth_no_account') }}</span> <a href="{{ route('signup') }}" class="text-slate-900 dark:text-white font-bold inline-block">{{ __('landing.auth_sign_up') }}</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!--end grid-->
            </div>
        </section>
        <!-- End Hero -->


        <!-- LTR & RTL Mode Code -->
        <div class="fixed top-1/3 -right-3 z-50">
            <a href="" id="switchRtl">
                <span class="py-1 px-3 relative inline-block rounded-t-md -rotate-90 bg-white dark:bg-slate-900 shadow-md dark:shadow dark:shadow-gray-800 font-semibold rtl:block ltr:hidden" >LTR</span>
                <span class="py-1 px-3 relative inline-block rounded-t-md -rotate-90 bg-white dark:bg-slate-900 shadow-md dark:shadow dark:shadow-gray-800 font-semibold ltr:block rtl:hidden">RTL</span>
            </a>
        </div>
        <!-- LTR & RTL Mode Code -->

        <!-- JAVASCRIPTS -->
        <script src="assets/libs/feather-icons/feather.min.js"></script>
        <script src="assets/js/plugins.init.js"></script>
        <script src="assets/js/app.js"></script>
        <!-- JAVASCRIPTS -->
    </body>
</html> 