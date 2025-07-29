<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="dark scroll-smooth" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>VERSEAI - Blog</title>
        <meta name="description" content="AI Blog & Articles">
        <meta name="keywords" content="AI, Blog, Articles, Technology">
        <meta name="author" content="VERSEAI">
        <meta name="version" content="1.0">
        <!-- favicon -->
        <link href="{{ asset('assets/images/favicon.ico') }}" rel="shortcut icon">

        <!-- Css -->
        <!-- Main Css -->
        <link href="{{ asset('assets/libs/@mdi/font/css/materialdesignicons.min.css') }}" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="{{ asset('assets/css/tailwind.css') }}">

    </head>
    
    <body class="font-figtree text-base text-slate-900 dark:text-white dark:bg-slate-900">
        <!-- Start Navbar -->
        <nav id="topnav" class="defaultscroll is-sticky">
            <div class="container">
                <!-- Logo container-->
                <a class="logo" href="{{ route('home') }}">
                    <span class="inline-block dark:hidden">
                        <img src="{{ asset('assets/images/logo-dark.png') }}" class="h-6 l-dark" alt="">
                        <img src="{{ asset('assets/images/logo-white.png') }}" class="h-6 l-light" alt="">
                    </span>
                    <img src="{{ asset('assets/images/logo-light.png') }}" class="h-6 hidden dark:inline-block" alt="">
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
                            <span class="py-[6px] px-4 md:inline hidden items-center justify-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400/5 hover:bg-amber-400 border border-amber-400/10 hover:border-amber-400 text-amber-400 hover:text-white font-semibold">Login</span>
                            <span class="py-[6px] px-4 inline md:hidden items-center justify-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400 hover:bg-amber-500 border border-amber-400 hover:border-amber-500 text-white font-semibold">Login</span>
                        </a>
                    </li>
            
                    <li class="md:inline hidden ps-1 mb-0 ">
                        <a href="{{ route('signup') }}" target="_blank" class="py-[6px] px-4 inline-block items-center justify-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400 hover:bg-amber-500 border border-amber-400 hover:border-amber-500 text-white font-semibold">Signup</a>
                    </li>
                </ul>
                <!--Login button End-->

                <div id="navigation">
                    <!-- Navigation Menu-->   
                    <ul class="navigation-menu nav-light">
                        <li><a href="{{ route('home') }}" class="sub-menu-item">Home</a></li>
                        <li><a href="{{ route('blog') }}" class="sub-menu-item">Blog</a></li>
                        <li><a href="{{ route('login') }}" class="sub-menu-item">Login</a></li>
                    </ul><!--end navigation menu-->
                </div><!--end navigation-->
            </div><!--end container-->
        </nav><!--end header-->
        <!-- End Navbar -->
        
        <!-- Start Hero -->
        <section class="relative md:py-44 py-32 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-slate-900/70"></div>
            <div class="container relative">
                <div class="grid grid-cols-1 text-center mt-6">
                    <div>
                        <h5 class="md:text-4xl text-3xl md:leading-normal leading-normal tracking-wider font-semibold text-white mb-0">
                            @if(isset($category))
                                {{ $category->name }} Articles
                            @else
                                Latest Blogs & News
                            @endif
                        </h5>
                    </div>

                    <ul class="tracking-[0.5px] mb-0 inline-block mt-5">
                        <li class="inline-block capitalize text-[15px] font-medium duration-500 ease-in-out text-white/50 hover:text-white"><a href="{{ route('home') }}">VERSEAI</a></li>
                        <li class="inline-block text-base text-white/50 mx-0.5 ltr:rotate-0 rtl:rotate-180"><i class="mdi mdi-chevron-right"></i></li>
                        <li class="inline-block capitalize text-[15px] font-medium duration-500 ease-in-out text-white" aria-current="page">
                            @if(isset($category))
                                {{ $category->name }}
                            @else
                                Blogs
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </section><!--end section-->
        <div class="relative">
            <div class="shape absolute sm:-bottom-px -bottom-[2px] start-0 end-0 overflow-hidden z-1 text-white dark:text-slate-900">
                <svg class="w-full h-auto scale-[2.0] origin-top" viewBox="0 0 2880 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z" fill="currentColor"></path>
                </svg>
            </div>
        </div>
        <!-- End Hero -->

        <!-- start -->
        <section class="relative md:py-24 py-16">
            <div class="container relative">
                <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 gap-6">
                    @forelse($posts as $post)
                    <div class="relative bg-white dark:bg-slate-900 p-4 rounded-md shadow dark:shadow-gray-700">
                        <img src="{{ asset($post->featured_image) }}" class="rounded-md shadow dark:shadow-gray-700" alt="{{ $post->title }}">
                        <div class="pt-4">
                            <div class="flex justify-between items-center">
                                <div class="space-x-1">
                                    @foreach($post->categories as $category)
                                    <a href="{{ route('blog.category', $category->slug) }}" class="bg-amber-400/10 text-amber-500 dark:text-amber-400 text-[12px] font-semibold px-2.5 py-0.5 rounded h-5">{{ $category->name }}</a>
                                    @endforeach
                                </div>

                                <span class="flex items-center"><i data-feather="clock" class="h-4 w-4"></i> <span class="ms-1 text-slate-400">{{ $post->reading_time }} min read</span></span>
                            </div>

                            <div class="mt-5">
                                <a href="{{ route('blog.detail', $post->slug) }}" class="text-lg font-semibold hover:text-amber-400">{{ $post->title }}</a>
                            </div>

                            <div class="mt-5 flex justify-between items-center">
                                <span class="flex items-center">
                                    <img src="{{ asset('assets/images/client/01.jpg') }}" class="h-7 w-7 rounded-full" alt="">
                                    <a href="#" class="ms-1 text-slate-400 hover:text-amber-400">{{ $post->user->name }}</a>
                                </span>

                                <span class="flex items-center"><i data-feather="calendar" class="h-4 w-4"></i> <span class="ms-1 text-slate-400">{{ $post->published_at->format('F j, Y') }}</span></span>
                            </div>
                        </div>
                    </div><!--end blog content-->
                    @empty
                    <div class="col-span-full text-center py-12">
                        <h3 class="text-xl font-semibold text-slate-400">No blog posts found.</h3>
                        <p class="text-slate-400 mt-2">Check back later for new articles.</p>
                    </div>
                    @endforelse
                </div><!--end grid-->

                @if($posts->hasPages())
                <div class="grid md:grid-cols-12 grid-cols-1 mt-8">
                    <div class="md:col-span-12 text-center">
                        <nav aria-label="Page navigation example">
                            <ul class="inline-flex items-center -space-x-px">
                                @if($posts->onFirstPage())
                                    <li>
                                        <span class="w-9 h-9 inline-flex text-sm justify-center items-center text-slate-400 bg-white dark:bg-slate-900 rounded-s-3xl border border-gray-100 dark:border-gray-800">
                                            <i class="mdi mdi-chevron-left text-[20px] rtl:rotate-180 rtl:-mt-1"></i>
                                        </span>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ $posts->previousPageUrl() }}" class="w-9 h-9 inline-flex text-sm justify-center items-center text-slate-400 bg-white dark:bg-slate-900 rounded-s-3xl hover:text-white border border-gray-100 dark:border-gray-800 hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400">
                                            <i class="mdi mdi-chevron-left text-[20px] rtl:rotate-180 rtl:-mt-1"></i>
                                        </a>
                                    </li>
                                @endif

                                @foreach($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                                    <li>
                                        <a href="{{ $url }}" class="w-9 h-9 inline-flex text-sm justify-center items-center {{ $page == $posts->currentPage() ? 'text-white bg-amber-400 border border-amber-400' : 'text-slate-400 hover:text-white bg-white dark:bg-slate-900 border border-gray-100 dark:border-gray-800 hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400' }}">{{ $page }}</a>
                                    </li>
                                @endforeach

                                @if($posts->hasMorePages())
                                    <li>
                                        <a href="{{ $posts->nextPageUrl() }}" class="w-9 h-9 inline-flex text-sm justify-center items-center text-slate-400 bg-white dark:bg-slate-900 rounded-e-3xl hover:text-white border border-gray-100 dark:border-gray-800 hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400">
                                            <i class="mdi mdi-chevron-right text-[20px] rtl:rotate-180 rtl:-mt-1"></i>
                                        </a>
                                    </li>
                                @else
                                    <li>
                                        <span class="w-9 h-9 inline-flex text-sm justify-center items-center text-slate-400 bg-white dark:bg-slate-900 rounded-e-3xl border border-gray-100 dark:border-gray-800">
                                            <i class="mdi mdi-chevron-right text-[20px] rtl:rotate-180 rtl:-mt-1"></i>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div><!--end col-->
                </div>
                @endif
            </div><!--end container-->
        </section><!--end section-->
        <!-- end -->

        <!-- Start Footer -->
        <div class="relative">
            <div class="shape absolute xl:-bottom-[30px] lg:-bottom-[16px] md:-bottom-[13px] -bottom-[5px] start-0 end-0 overflow-hidden z-1 rotate-180 text-white dark:text-slate-900">
                <svg class="w-full h-auto scale-[2.0] origin-top" viewBox="0 0 2880 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z" fill="currentColor"></path>
                </svg>
            </div>
        </div>
        <footer class="relative bg-gray-900 overflow-hidden">
            <span class="absolute blur-[200px] w-[300px] h-[300px] rounded-full top-0 -start-[0] bg-gradient-to-tl to-amber-400  from-fuchsia-600 z-0"></span>
            <div class="container-fluid relative md:py-24 py-16">
                <div class="container relative">
                    <div class="grid grid-cols-1 text-center">
                        <div class="">
                            <h4 class="font-bold lg:leading-normal leading-normal text-4xl lg:text-5xl text-white tracking-normal mb-4">Start Your Free Trail.</h4>
                            <p class="text-white/70 text-lg max-w-xl mx-auto">Artificial intelligence makes it fast easy to create content for your blog, social media, website, and more!</p>

                            <div class="mt-6">
                                <a href="{{ route('signup') }}" class="py-2 px-5 inline-block font-semibold tracking-wide border align-middle duration-500 text-base text-center bg-transparent hover:bg-amber-400 border-gray-800 dark:border-slate-800 hover:border-amber-400 dark:hover:border-amber-400 text-white rounded-md">Join Now!</a>
                            </div>
                        </div>
                    </div><!--end grid-->
                </div><!--end container-->
            </div><!--end container fluid-->

            <div class="container relative text-center">
                <div class="grid grid-cols-1 border-t border-gray-800 dark:border-slate-800">
                    <div class="py-[30px] px-0">
                        <div class="grid md:grid-cols-2 items-center">
                            <div class="md:text-start text-center">
                                <a href="#" class="text-[22px] focus:outline-none">
                                    <img src="{{ asset('assets/images/logo-light.png') }}" class="mx-auto md:me-auto md:ms-0" alt="">
                                </a>
                            </div>

                            <ul class="list-none footer-list md:text-end text-center mt-6 md:mt-0">
                                <li class="inline"><a href="#" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="shopping-cart" class="h-4 w-4 align-middle" title="Buy Now"></i></a></li>
                                <li class="inline"><a href="#" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="dribbble" class="h-4 w-4 align-middle" title="dribbble"></i></a></li>
                                <li class="inline"><a href="#" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="linkedin" class="h-4 w-4 align-middle" title="Linkedin"></i></a></li>
                                <li class="inline"><a href="#" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="facebook" class="h-4 w-4 align-middle" title="facebook"></i></a></li>
                                <li class="inline"><a href="#" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="instagram" class="h-4 w-4 align-middle" title="instagram"></i></a></li>
                                <li class="inline"><a href="#" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="twitter" class="h-4 w-4 align-middle" title="twitter"></i></a></li>
                                <li class="inline"><a href="mailto:support@verseai.com" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="mail" class="h-4 w-4 align-middle" title="email"></i></a></li>
                            </ul><!--end icon-->
                        </div><!--end grid-->
                    </div>
                </div><!--end grid-->
            </div><!--end container-->

            <div class="py-[30px] px-0 border-t border-gray-800 dark:border-slate-800">
                <div class="container relative text-center">
                    <div class="grid grid-cols-1">
                        <div class="text-center">
                            <p class="text-gray-400">© <script>document.write(new Date().getFullYear())</script> VERSEAI. All rights reserved.</p>
                        </div>
                    </div><!--end grid-->
                </div><!--end container-->
            </div>
        </footer><!--end footer-->
        <!-- End Footer -->

        <!-- Back to top -->
        <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fixed hidden text-lg rounded z-10 bottom-5 end-5 h-9 w-9 text-center bg-amber-400 hover:bg-amber-500 text-white leading-9 justify-center items-center duration-500"><i data-feather="arrow-up" class="h-4 w-4 stroke-[3]"></i></a>
        <!-- Back to top -->

        <!-- JAVASCRIPTS -->
        <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins.init.js') }}"></script>
        <script src="{{ asset('assets/js/app.js') }}"></script>
        <!-- JAVASCRIPTS -->
    </body>
</html> 