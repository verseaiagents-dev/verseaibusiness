<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="dark scroll-smooth" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $post->title }} - VERSEAI</title>
        <meta name="description" content="{{ $post->excerpt }}">
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
        <section class="relative md:pt-44 pt-36 bg-gradient-to-b from-amber-400/20 dark:from-amber-400/40 to-transparent">
            <div class="container relative">
                <div class="md:flex justify-center">
                    <div class="lg:w-2/3 md:w-4/5">
                        @foreach($post->categories as $category)
                        <a href="{{ route('blog.category', $category->slug) }}" class="bg-amber-400 text-white text-[12px] font-semibold px-2.5 py-0.5 rounded h-5">{{ $category->name }}</a>
                        @endforeach
                        <h5 class="md:text-4xl text-3xl font-bold md:tracking-normal tracking-normal md:leading-normal leading-normal mt-3">{{ $post->title }}</h5>
                        <p class="text-slate-400 text-lg mt-3">{{ $post->excerpt }}</p>

                        <div class="flex items-center mt-5">
                            <img src="{{ asset('assets/images/client/01.jpg') }}" class="h-12 w-12 rounded-full" alt="">

                            <div class="ms-2">
                                <h6><a href="#" class="font-medium hover:text-amber-400">{{ $post->user->name }}</a><a href="#" class="ms-1 text-green-600 font-medium"><i class="mdi mdi-circle-medium"></i>Follow</a></h6>
                                <span class="text-slate-400 text-sm">{{ $post->published_at->format('F j, Y') }} <span><i class="mdi mdi-circle-medium"></i>{{ $post->reading_time }} min read</span></span>
                            </div>
                        </div>
                    </div><!--end width-->
                </div><!--end flex-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End Hero -->
        
        <!-- Start -->
        <section class="relative md:pb-24 pb-16 pt-7">
            <div class="container relative">
                <div class="md:flex justify-center">
                    <div class="lg:w-2/3 md:w-4/5">
                        <img src="{{ asset($post->featured_image) }}" class="rounded-md" alt="{{ $post->title }}">

                        <div class="mt-4 prose prose-lg dark:prose-invert max-w-none">
                            {!! nl2br(e($post->content)) !!}
                        </div>

                        <div class="flex justify-between py-4 border-y border-gray-100 dark:border-gray-700 mt-5">
                            <ul class="list-none">
                                <li class="inline"><a href="javascript:void(0)" class="inline-flex items-center text-slate-400"><i data-feather="heart" class="h-4 w-4 text-slate-900 dark:text-white hover:text-amber-400 me-1"></i> {{ $post->likes_count }}</a></li>
                                <li class="inline ms-2"><a href="javascript:void(0)" class="inline-flex items-center text-slate-400"><i data-feather="message-circle" class="h-4 w-4 text-slate-900 dark:text-white hover:text-amber-400 me-1"></i> {{ $post->comments_count }}</a></li>
                            </ul>

                            <ul class="list-none">
                                <li class="inline"><a href="javascript:void(0)" class="inline-flex items-center text-slate-400"><i data-feather="share-2" class="h-4 w-4 text-slate-900 dark:text-white hover:text-amber-400"></i></a></li>
                            </ul>
                        </div>

                        @if($post->approvedComments->count() > 0)
                        <div class="mt-6">
                            <h5 class="text-xl font-semibold">Comments:</h5>

                            @foreach($post->approvedComments->whereNull('parent_id') as $comment)
                            <div class="mt-8">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <img src="{{ asset('assets/images/client/01.jpg') }}" class="h-11 w-11 rounded-full shadow" alt="">

                                        <div class="ms-3 flex-1">
                                            <a href="#" class="font-semibold hover:text-amber-400 duration-500">{{ $comment->author_name }}</a>
                                            <p class="text-sm text-slate-400">{{ $comment->created_at->format('F j, Y \a\t g:i A') }}</p>
                                        </div>
                                    </div>

                                    <a href="#" class="text-slate-400 hover:text-amber-400 duration-500 ms-5"><i class="mdi mdi-reply"></i> Reply</a>
                                </div>
                                <div class="p-4 bg-gray-50 dark:bg-slate-800 rounded-md shadow dark:shadow-gray-800 mt-6">
                                    <p class="text-slate-400 italic">"{{ $comment->content }}"</p>
                                </div>

                                @if($comment->replies->count() > 0)
                                    @foreach($comment->replies->where('status', 'approved') as $reply)
                                    <div class="mt-4 ms-8">
                                        <div class="flex items-center">
                                            <img src="{{ asset('assets/images/client/02.jpg') }}" class="h-9 w-9 rounded-full shadow" alt="">
                                            <div class="ms-3">
                                                <a href="#" class="font-semibold hover:text-amber-400 duration-500">{{ $reply->author_name }}</a>
                                                <p class="text-sm text-slate-400">{{ $reply->created_at->format('F j, Y \a\t g:i A') }}</p>
                                            </div>
                                        </div>
                                        <div class="p-3 bg-gray-50 dark:bg-slate-800 rounded-md shadow dark:shadow-gray-800 mt-3">
                                            <p class="text-slate-400 italic">"{{ $reply->content }}"</p>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <div class="mt-6">
                            <h5 class="text-xl font-semibold">Leave A Comment:</h5>

                            <form class="mt-6" action="{{ route('api.comments.guest') }}" method="POST">
                                @csrf
                                <input type="hidden" name="blog_post_id" value="{{ $post->id }}">
                                <div class="grid lg:grid-cols-12 lg:gap-6">
                                    <div class="lg:col-span-6 mb-5">
                                        <div class="text-start">
                                            <label for="guest_name" class="font-semibold">Your Name:</label>
                                            <div class="form-icon relative mt-2">
                                                <i data-feather="user" class="w-4 h-4 absolute top-3 start-4"></i>
                                                <input name="guest_name" id="guest_name" type="text" class="form-input ps-11 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-200 focus:border-amber-400 dark:border-gray-800 dark:focus:border-amber-400 focus:ring-0" placeholder="Name :" required>
                                            </div>
                                        </div>
                                    </div>
    
                                    <div class="lg:col-span-6 mb-5">
                                        <div class="text-start">
                                            <label for="guest_email" class="font-semibold">Your Email:</label>
                                            <div class="form-icon relative mt-2">
                                                <i data-feather="mail" class="w-4 h-4 absolute top-3 start-4"></i>
                                                <input name="guest_email" id="guest_email" type="email" class="form-input ps-11 w-full py-2 px-3 h-10 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-200 focus:border-amber-400 dark:border-gray-800 dark:focus:border-amber-400 focus:ring-0" placeholder="Email :" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1">
                                    <div class="mb-5">
                                        <div class="text-start">
                                            <label for="content" class="font-semibold">Your Comment:</label>
                                            <div class="form-icon relative mt-2">
                                                <i data-feather="message-circle" class="w-4 h-4 absolute top-3 start-4"></i>
                                                <textarea name="content" id="content" class="form-input ps-11 w-full py-2 px-3 h-28 bg-transparent dark:bg-slate-900 dark:text-slate-200 rounded outline-none border border-gray-200 focus:border-amber-400 dark:border-gray-800 dark:focus:border-amber-400 focus:ring-0" placeholder="Message :" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" id="submit" name="send" class="py-2 px-5 inline-block tracking-wide border align-middle duration-500 text-base text-center bg-amber-400 hover:bg-amber-500 border-amber-400 hover:border-amber-500 text-white rounded-md w-full">Send Message</button>
                            </form>
                        </div>

                        @if($relatedPosts->count() > 0)
                        <div class="mt-12">
                            <h5 class="text-xl font-semibold mb-6">Related Articles:</h5>
                            <div class="grid md:grid-cols-3 gap-6">
                                @foreach($relatedPosts as $relatedPost)
                                <div class="bg-white dark:bg-slate-900 p-4 rounded-md shadow dark:shadow-gray-700">
                                    <img src="{{ asset($relatedPost->featured_image) }}" class="rounded-md shadow dark:shadow-gray-700 mb-4" alt="{{ $relatedPost->title }}">
                                    <h6 class="font-semibold hover:text-amber-400 mb-2">
                                        <a href="{{ route('blog.detail', $relatedPost->slug) }}">{{ $relatedPost->title }}</a>
                                    </h6>
                                    <p class="text-slate-400 text-sm">{{ Str::limit($relatedPost->excerpt, 100) }}</p>
                                    <div class="flex items-center mt-3 text-sm text-slate-400">
                                        <i data-feather="calendar" class="h-4 w-4 me-1"></i>
                                        {{ $relatedPost->published_at->format('M j, Y') }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div><!--end flex-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- End -->

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