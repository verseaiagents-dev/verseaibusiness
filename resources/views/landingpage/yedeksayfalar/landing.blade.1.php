<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth" dir="ltr">
    <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>VersAI - İşletmenize Özel Yapay Zeka Agent Platformu</title>
     <meta name="description" content="VersAI, işletmeler için özelleştirilebilir yapay zeka agentları sunar. E-ticaret, turizm ve emlak sektörlerinde müşteri etkileşimini artırın, web sitenize embed.js ile kolayca entegre edin.">
     <meta name="keywords" content="VersAI, AI Agent, Yapay Zeka Chatbot, E-ticaret AI, Turizm AI, Emlak AI, Embed.js, Chrome Extension, AI İşletme Çözümleri">
     <meta name="author" content="VersAI Team">
     <meta name="website" content="https://vers.ai">
     <meta name="email" content="support@vers.ai">
     <meta name="version" content="1.0">
     
        <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.youtube.com https://www.youtube-nocookie.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; media-src 'self' https:; connect-src 'self' https:; frame-src 'self' https://www.youtube-nocookie.com;">
        <!-- favicon -->
        <link href="{{ asset('landingpage/assets/images/favicon.ico') }}" rel="shortcut icon">

        <!-- Css -->
        <link href="{{ asset('landingpage/assets/libs/tobii/css/tobii.min.css') }}" rel="stylesheet">
        <!-- Main Css -->
        <link href="{{ asset('landingpage/assets/libs/@mdi/font/css/materialdesignicons.min.css') }}" rel="stylesheet" type="text/css">
        <!-- Tailwind CSS -->
        <link href="{{ asset('landingpage/assets/css/tailwind.css') }}" rel="stylesheet" type="text/css">

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
                        <h4 class="mb-0 mt-2 text-lg font-semibold">Mortal.Ai</h4>
                    </div>
                </div>
            </div>
        </div> -->
        <!-- Loader End -->
        
        <!-- Start Navbar -->
        <nav id="topnav" class="defaultscroll is-sticky">
            <div class="container">
                <!-- Logo container-->
                <a class="logo" href="index.html">
                    <!-- <div class="block sm:hidden">
                        <img src="{{ asset('landingpage/assets/images/logo-icon-40.png') }}" class="h-10 inline-block dark:hidden"  alt="">
                        <img src="{{ asset('landingpage/assets/images/logo-icon-40-white.png') }}" class="h-10 hidden dark:inline-block"  alt="">
                    </div> -->
                    <!-- <div class="sm:block hidden"> -->
                        <img src="{{ asset('landingpage/assets/images/logo-dark.png') }}" class="h-6 inline-block dark:hidden" alt="">
                        <img src="{{ asset('landingpage/assets/images/logo-white.png') }}" class="h-6 hidden dark:inline-block" alt="">
                    <!-- </div> -->
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
                        <a href="login.html">
                            <span class="py-[6px] px-4 md:inline hidden items-center justify-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400/5 hover:bg-amber-400 border border-amber-400/10 hover:border-amber-400 text-amber-400 hover:text-white font-semibold">Giriş yap</span>
                            <span class="py-[6px] px-4 inline md:hidden items-center justify-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400 hover:bg-amber-500 border border-amber-400 hover:border-amber-500 text-white font-semibold">Giriş yap</span>
                        </a>
                    </li>
            
                    <li class="md:inline hidden ps-1 mb-0 ">
                        <a href="Kayıt Ol.html" target="_blank" class="py-[6px] px-4 inline-block items-center justify-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400 hover:bg-amber-500 border border-amber-400 hover:border-amber-500 text-white font-semibold">Kayıt Ol</a>
                    </li>
                </ul>
                <!--Login button End-->

             
            </div><!--end container-->
        </nav><!--end header-->
        <!-- End Navbar -->

        <!-- Start Hero -->
        <section class="relative overflow-hidden pt-48 after:content-[''] after:absolute after:inset-0 after:mx-auto after:w-[56rem] after:h-[56rem] after:bg-gradient-to-tl after:to-amber-400/30  after:from-fuchsia-600/30 dark:after:to-amber-400/50 dark:after:from-fuchsia-600/50 after:blur-[200px] after:rounded-full after:-z-1">
            <div class="container relative z-2">
                <div class="grid grid-cols-1 text-center">
                    <div class="">
                         <h4 class="font-bold lg:leading-normal leading-normal text-4xl lg:text-6xl mb-5">
                              İşletmeniz İçin <br> 
                              <span class="typewrite bg-gradient-to-br from-amber-400 to-fuchsia-600 text-transparent bg-clip-text" data-period="2000" data-type='[ "Yapay Zeka Asistanı", "Akıllı Chatbot", "Müşteri Otomasyonu" ]'> 
                                  <span class="wrap"></span>
                              </span>
                          </h4>
                          <p class="text-slate-400 dark:text-white/60 text-lg max-w-xl mx-auto">
                              VersAI, müşteri etkileşimini artırmak ve satış süreçlerini otomatikleştirmek için tasarlanmış özelleştirilebilir yapay zeka agentları sunar. Embed.js ile web sitenize entegre edin, Chrome Extension ile eventleri takip edin.
                          </p>
                          <div class="mt-6">
                              <a href="" class="py-2 px-5 inline-block font-semibold tracking-wide border align-middle duration-500 text-base text-center bg-amber-400 hover:bg-amber-500 border-amber-400 hover:border-amber-500 text-white rounded-md">Ücretsiz Dene</a>
                              <p class="text-slate-400 dark:text-white/60 text-sm mt-3">Kredi kartı gerekmez. 50 ücretsiz token ile başla.</p>
                          </div>
                          
                    </div>
                    <div class="relative mt-8 z-3">
                        <img src="{{ asset('landingpage/assets/images/classic01.png') }}" alt="" class="mover">
                    </div>
                </div><!--end grid-->
            </div>
            
            <div class="relative after:content-[''] after:absolute lg:after:-bottom-40 after:-bottom-28 after:end-0 after:start-0 after:mx-auto xl:after:w-[56rem] lg:after:w-[48rem] md:after:w-[32rem] after:w-[22rem] xl:after:h-[56rem] lg:after:h-[48rem] md:after:h-[32rem] after:h-[22rem] after:border-2 after:border-dashed after:border-slate-700/10 dark:after:border-slate-200/10 after:rounded-full after:-z-1 before:content-[''] before:absolute lg:before:-bottom-72 before:-bottom-56 before:end-0 before:start-0 before:mx-auto xl:before:w-[72rem] lg:before:w-[64rem] md:before:w-[48rem] before:w-[24rem] xl:before:h-[72rem] lg:before:h-[64rem] md:before:h-[48rem] before:h-[24rem] before:border-2 before:border-dashed before:border-slate-700/10 dark:before:border-slate-200/10 before:rounded-full before:-z-1"></div>
        </section>
        <!-- End Hero -->

        <!-- Business Partner -->
        <section class="pt-6">
            <div class="container relative">
                <div class="grid md:grid-cols-6 grid-cols-2 justify-center gap-6">
                    <div class="mx-auto py-4">
                        <img src="{{ asset('landingpage/assets/images/client/amazon.svg') }}" class="h-8 md:h-12" alt="Amazon" style="min-height: 24px;">
                    </div>

                    <div class="mx-auto py-4">
                        <img src="{{ asset('landingpage/assets/images/client/google.svg') }}" class="h-8 md:h-12" alt="Google" style="min-height: 24px;">
                    </div>
                    
                    <div class="mx-auto py-4">
                        <img src="{{ asset('landingpage/assets/images/client/lenovo.svg') }}" class="h-8 md:h-12" alt="Lenovo" style="min-height: 24px;">
                    </div>
                    
                    <div class="mx-auto py-4">
                        <img src="{{ asset('landingpage/assets/images/client/paypal.svg') }}" class="h-8 md:h-12" alt="PayPal" style="min-height: 24px;">
                    </div>
                    
                    <div class="mx-auto py-4">
                        <img src="{{ asset('landingpage/assets/images/client/shopify.svg') }}" class="h-8 md:h-12" alt="Shopify" style="min-height: 24px;">
                    </div>
                    
                    <div class="mx-auto py-4">
                        <img src="{{ asset('landingpage/assets/images/client/spotify.svg') }}" class="h-8 md:h-12" alt="Spotify" style="min-height: 24px;">
                    </div>
                </div><!--end grid-->
            </div><!--end container-->
        </section><!--end section-->
        <!-- Business Partner -->

        <!-- Start -->
        <section class="relative md:py-24 py-16">
            <div class="container relative">
                <div class="grid grid-cols-1 pb-6 text-center">
                    <h3 class="mb-4 md:text-3xl md:leading-normal text-2xl leading-normal font-semibold">
                         AI + Otomasyon = <br> 
                         <span class="bg-gradient-to-br from-amber-400 to-fuchsia-600 text-transparent bg-clip-text">VersAI Gücü</span>
                     </h3>
                     <p class="text-slate-400 max-w-xl mx-auto">
                         VersAI, e-ticaret, turizm ve emlak gibi sektörlerde müşterilerinize akıllı öneriler sunar, sipariş yönetimini hızlandırır ve satış dönüşüm oranlarını artırır.
                     </p>
               </div><!--end grid-->

                <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 mt-6 gap-6">
                    <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-md shadow dark:shadow-gray-800">
                        <div class="p-6 pb-0 relative overflow-hidden after:content-[''] after:absolute after:inset-0 after:mx-auto after:w-72 after:h-72 after:bg-gradient-to-tl after:to-amber-400 after:from-fuchsia-600 after:blur-[80px] after:rounded-full">
                            <img src="{{ asset('landingpage/assets/images/features/video-1.png') }}" class="relative rounded-t-md shadow-md dark:shadow-slate-700 z-1" alt="">
                        </div>

                        <div class="p-6">
                         <h5 class="text-lg font-semibold">%35 Daha Yüksek Dönüşüm Oranı</h5>
<p class="text-slate-400 mt-3">
    VersAI kullanan işletmeler, chatbot destekli otomasyon sayesinde ortalama %35 daha fazla müşteri dönüşümü elde ediyor.
</p>

                         </div>
                    </div><!--end content-->

                    <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-md shadow dark:shadow-gray-800">
                        <div class="p-6 pb-0 relative overflow-hidden after:content-[''] after:absolute after:inset-0 after:mx-auto after:w-72 after:h-72 after:bg-gradient-to-tl after:to-amber-400 after:from-fuchsia-600 after:blur-[80px] after:rounded-full">
                            <img src="{{ asset('landingpage/assets/images/features/video-2.png') }}" class="relative rounded-t-md shadow-md dark:shadow-slate-700 z-1" alt="">
                        </div>

                        <div class="p-6">
                         <h5 class="text-lg font-semibold">%40 Daha Fazla Etkileşim</h5>
<p class="text-slate-400 mt-3">
    Web sitenizde AI destekli canlı sohbet ile ziyaretçilerinizi anında karşılayın ve etkileşimi artırın.
</p>

                         </div>
                    </div><!--end content-->

                    <div class="relative overflow-hidden bg-white dark:bg-slate-900 rounded-md shadow dark:shadow-gray-800">
                        <div class="p-6 pb-0 relative overflow-hidden after:content-[''] after:absolute after:inset-0 after:mx-auto after:w-72 after:h-72 after:bg-gradient-to-tl after:to-amber-400 after:from-fuchsia-600 after:blur-[80px] after:rounded-full">
                            <img src="{{ asset('landingpage/assets/images/features/video-3.png') }}" class="relative rounded-t-md shadow-md dark:shadow-slate-700 z-1" alt="">
                        </div>

                        <div class="p-6">
                         <h5 class="text-lg font-semibold">Gerçek Zamanlı Yanıtlar</h5>
                         <p class="text-slate-400 mt-3">
                             Müşterilerinizin sorularına anında cevap verin. Otomatik öneriler, ürün karşılaştırmaları ve sepet optimizasyonu tek bir AI ile mümkün.
                         </p>
                         
                         </div>
                    </div><!--end content-->
                </div><!--end grid-->
            </div><!--end container-->

            <div class="container relative md:mt-24 mt-16">
                <div class="grid md:grid-cols-2 grid-cols-1 items-center gap-6">
                                            <div class="relative overflow-hidden rounded-lg border border-amber-400/5 bg-gradient-to-tl to-amber-400/30  from-fuchsia-600/30 dark:to-amber-400/50 dark:from-fuchsia-600/50 ps-6 pt-6 lg:me-8">
                            <img src="{{ asset('landingpage/assets/images/features/1.png') }}" class="ltr:rounded-tl-lg rtl:rounded-tr-lg" alt="">
                        </div>

                    <div class="">
                         <h3 class="mb-4 md:text-3xl md:leading-normal text-2xl leading-normal font-semibold">
                              Tek Tıkla AI Agent’ınızı Eğitin ve <br> 
                              <span class="bg-gradient-to-br from-amber-400 to-fuchsia-600 text-transparent bg-clip-text">Müşterilerinizi Dönüştürün</span>
                          </h3>
                          <p class="text-slate-400 max-w-xl mx-auto">
                              VersAI ile chatbot’unuzu eğitin, web sitenizde müşteri sorularını yanıtlasın, ürünleri önerip satış dönüşümlerini artırın. Embed.js entegrasyonu ve Chrome Extension desteği ile dakikalar içinde kullanıma hazır.
                          </p>
                          
                        <ul class="list-none text-slate-400 mt-4">
                            <li class="mb-2 flex items-center"><i data-feather="check-circle" class="text-amber-400 h-5 w-5 me-2"></i> Digital Marketing Solutions for Tomorrow</li>
                            <li class="mb-2 flex items-center"><i data-feather="check-circle" class="text-amber-400 h-5 w-5 me-2"></i> Our Talented & Experienced Marketing Agency</li>
                            <li class="mb-2 flex items-center"><i data-feather="check-circle" class="text-amber-400 h-5 w-5 me-2"></i> Create your own skin to match your brand</li>
                        </ul>

                        <div class="mt-4">
                            <a href="" class="hover:text-amber-400 font-medium duration-500">Find Out More <i class="mdi mdi-chevron-right text-[20px] align-middle"></i></a>
                        </div>
                    </div>
                </div><!--end grid-->
            </div><!--end container-->

            <div class="container relative md:mt-24 mt-16">
                <div class="grid md:grid-cols-2 grid-cols-1 items-center gap-6">
                    <div class="relative order-1 md:order-2">
                        <div class="relative overflow-hidden rounded-lg border border-amber-400/5 bg-gradient-to-tl to-amber-400/30  from-fuchsia-600/30 dark:to-amber-400/50 dark:from-fuchsia-600/50 pe-6 pt-6 lg:ms-8">
                            <img src="{{ asset('landingpage/assets/images/features/2.png') }}" class="ltr:rounded-tr-lg rtl:rounded-tl-lg" alt="">
                        </div>
                    </div>

                    <div class="order-2 md:order-1">
                        <h4 class="mb-4 md:text-3xl md:leading-normal text-2xl leading-normal font-semibold">Write Blog Posts, <br> Stories, & Even Books</h4>
                        <p class="text-slate-400">"Usually, our colleagues don't jump in the air when they hear e-learning, but the AI videos created with Mortal.Ai have sparked motivation that we haven't seen before."</p>
                        <ul class="list-none text-slate-400 mt-4">
                            <li class="mb-2 flex items-center"><i data-feather="check-circle" class="text-amber-400 h-5 w-5 me-2"></i> Digital Marketing Solutions for Tomorrow</li>
                            <li class="mb-2 flex items-center"><i data-feather="check-circle" class="text-amber-400 h-5 w-5 me-2"></i> Our Talented & Experienced Marketing Agency</li>
                            <li class="mb-2 flex items-center"><i data-feather="check-circle" class="text-amber-400 h-5 w-5 me-2"></i> Create your own skin to match your brand</li>
                        </ul>

                        <div class="mt-4">
                            <a href="" class="hover:text-amber-400 font-medium duration-500">Find Out More <i class="mdi mdi-chevron-right text-[20px] align-middle"></i></a>
                        </div>
                    </div>
                </div>
            </div><!--end container-->

            <div class="container relative md:mt-24 mt-16">
                <div class="grid grid-cols-1 pb-6 text-center">
                    <h3 class="mb-4 md:text-3xl md:leading-normal text-2xl leading-normal font-semibold">Amazing Features</h3>

                    <p class="text-slate-400 max-w-xl mx-auto">Artificial intelligence makes it fast easy to create content for your blog, social media, website, and more!</p>
                </div><!--end grid-->

                <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 mt-6 gap-6">
                    <div class="px-6 py-10 shadow hover:shadow-md dark:shadow-gray-800 dark:hover:shadow-gray-700 duration-500 rounded-lg bg-white dark:bg-slate-900">
                        <i class="mdi mdi-flip-horizontal text-4xl bg-gradient-to-tl to-amber-400 from-fuchsia-600 text-transparent bg-clip-text"></i>

                        <div class="content mt-7">
                            <a href="" class="title h5 text-lg font-medium hover:text-amber-400 duration-500">Plagiarism checker</a>
                            <p class="text-slate-400 mt-3">The phrasal sequence of the is now so that many campaign and benefit</p>
                            
                            <div class="mt-5">
                                <a href="" class="hover:text-amber-400 font-medium duration-500">Read More <i class="mdi mdi-arrow-right align-middle"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-10 shadow hover:shadow-md dark:shadow-gray-800 dark:hover:shadow-gray-700 duration-500 rounded-lg bg-white dark:bg-slate-900">
                        <i class="mdi mdi-email-edit-outline text-4xl bg-gradient-to-tl to-amber-400 from-fuchsia-600 text-transparent bg-clip-text"></i>

                        <div class="content mt-7">
                            <a href="" class="title h5 text-lg font-medium hover:text-amber-400 duration-500">Content Generator</a>
                            <p class="text-slate-400 mt-3">The phrasal sequence of the is now so that many campaign and benefit</p>
                            
                            <div class="mt-5">
                                <a href="" class="hover:text-amber-400 font-medium duration-500">Read More <i class="mdi mdi-arrow-right align-middle"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-10 shadow hover:shadow-md dark:shadow-gray-800 dark:hover:shadow-gray-700 duration-500 rounded-lg bg-white dark:bg-slate-900">
                        <i class="mdi mdi-star-outline text-4xl bg-gradient-to-tl to-amber-400 from-fuchsia-600 text-transparent bg-clip-text"></i>

                        <div class="content mt-7">
                            <a href="" class="title h5 text-lg font-medium hover:text-amber-400 duration-500">Search Engine Optimization</a>
                            <p class="text-slate-400 mt-3">The phrasal sequence of the is now so that many campaign and benefit</p>
                            
                            <div class="mt-5">
                                <a href="" class="hover:text-amber-400 font-medium duration-500">Read More <i class="mdi mdi-arrow-right align-middle"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-10 shadow hover:shadow-md dark:shadow-gray-800 dark:hover:shadow-gray-700 duration-500 rounded-lg bg-white dark:bg-slate-900">
                        <i class="mdi mdi-bookmark-outline text-4xl bg-gradient-to-tl to-amber-400 from-fuchsia-600 text-transparent bg-clip-text"></i>

                        <div class="content mt-7">
                            <a href="" class="title h5 text-lg font-medium hover:text-amber-400 duration-500">Digital name generator</a>
                            <p class="text-slate-400 mt-3">The phrasal sequence of the is now so that many campaign and benefit</p>
                            
                            <div class="mt-5">
                                <a href="" class="hover:text-amber-400 font-medium duration-500">Read More <i class="mdi mdi-arrow-right align-middle"></i></a>
                            </div>
                        </div>
                    </div>
                
                    <div class="px-6 py-10 shadow hover:shadow-md dark:shadow-gray-800 dark:hover:shadow-gray-700 duration-500 rounded-lg bg-white dark:bg-slate-900">
                        <i class="mdi mdi-account-check-outline text-4xl bg-gradient-to-tl to-amber-400 from-fuchsia-600 text-transparent bg-clip-text"></i>

                        <div class="content mt-7">
                            <a href="" class="title h5 text-lg font-medium hover:text-amber-400 duration-500">Ad Targeting tips</a>
                            <p class="text-slate-400 mt-3">The phrasal sequence of the is now so that many campaign and benefit</p>
                            
                            <div class="mt-5">
                                <a href="" class="hover:text-amber-400 font-medium duration-500">Read More <i class="mdi mdi-arrow-right align-middle"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-10 shadow hover:shadow-md dark:shadow-gray-800 dark:hover:shadow-gray-700 duration-500 rounded-lg bg-white dark:bg-slate-900">
                        <i class="mdi mdi-comment-outline text-4xl bg-gradient-to-tl to-amber-400 from-fuchsia-600 text-transparent bg-clip-text"></i>

                        <div class="content mt-7">
                            <a href="" class="title h5 text-lg font-medium hover:text-amber-400 duration-500">Content Rewriter</a>
                            <p class="text-slate-400 mt-3">The phrasal sequence of the is now so that many campaign and benefit</p>
                            
                            <div class="mt-5">
                                <a href="" class="hover:text-amber-400 font-medium duration-500">Read More <i class="mdi mdi-arrow-right align-middle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--end container-->

            <div class="container relative md:mt-24 mt-16">
                <div class="grid md:grid-cols-2 grid-cols-1 items-center gap-6">
                    <div class="relative overflow-hidden after:content-[''] after:absolute after:inset-0 after:mx-auto after:w-72 after:h-72 after:bg-gradient-to-tl after:to-amber-400 after:from-fuchsia-600 after:blur-[80px] after:rounded-full p-6 bg-white dark:bg-slate-900 rounded-md shadow dark:shadow-slate-800">
                        <div class="relative overflow-hidden rounded-lg shadow-md dark:shadow-gray-800 z-1">
                            <div class="relative">
                                <video controls autoplay loop>
                                    <source src="{{ asset('landingpage/assets/images/modern.mp4') }}" type="video/mp4">
                                </video>
                                <a href="" class="absolute top-2 start-2 rounded-full p-0.5 bg-white dark:bg-slate-900 shadow dark:shadow-slate-800 z-10"><img src="{{ asset('landingpage/assets/images/flags/germany.png') }}" class="h-7 w-7 rounded-full" alt=""></a>
                            </div>
    
                            <div class="absolute bottom-2/4 translate-y-2/4 start-0 end-0 text-center">
                                <a href="#!" data-type="youtube" data-id="S_CGed6E610" class="lightbox lg:h-16 h-14 lg:w-16 w-14 rounded-full shadow-lg dark:shadow-gray-800 inline-flex items-center justify-center bg-white dark:bg-slate-900 hover:bg-amber-400 dark:hover:bg-amber-400 text-amber-400 hover:text-white duration-500 ease-in-out mx-auto">
                                    <i class="mdi mdi-play inline-flex items-center justify-center text-2xl"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <a href="" class="py-[6px] px-2 inline-flex items-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400/5 hover:bg-amber-400 border border-amber-400/10 hover:border-amber-400 text-amber-400 hover:text-white font-semibold m-0.5"><img src="{{ asset('landingpage/assets/images/flags/italy.png') }}" class="h-5 w-5 me-1" alt=""> Italian</a>
                            <a href="" class="py-[6px] px-2 inline-flex items-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400/5 hover:bg-amber-400 border border-amber-400/10 hover:border-amber-400 text-amber-400 hover:text-white font-semibold m-0.5"><img src="{{ asset('landingpage/assets/images/flags/india.png') }}" class="h-5 w-5 me-1" alt=""> Hindi</a>
                            <a href="" class="py-[6px] px-2 inline-flex items-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400/5 hover:bg-amber-400 border border-amber-400/10 hover:border-amber-400 text-amber-400 hover:text-white font-semibold m-0.5"><img src="{{ asset('landingpage/assets/images/flags/russia.png') }}" class="h-5 w-5 me-1" alt=""> Russian</a>
                            <a href="" class="py-[6px] px-2 inline-flex items-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400/5 hover:bg-amber-400 border border-amber-400/10 hover:border-amber-400 text-amber-400 hover:text-white font-semibold m-0.5"><img src="{{ asset('landingpage/assets/images/flags/spain.png') }}" class="h-5 w-5 me-1" alt=""> Spanish</a>
                            <a href="" class="py-[6px] px-2 inline-flex items-center tracking-wider align-middle duration-500 text-sm text-center rounded bg-amber-400/5 hover:bg-amber-400 border border-amber-400/10 hover:border-amber-400 text-amber-400 hover:text-white font-semibold m-0.5"><img src="{{ asset('landingpage/assets/images/flags/usa.png') }}" class="h-5 w-5 me-1" alt=""> English</a>
                        </div>
                    </div>

                    <div class="">
                         <h4 class="mb-4 md:text-3xl md:leading-normal text-2xl leading-normal font-semibold">
                              AI Agent ile <br> Müşteri Sorularını Yönetin
                          </h4>
                          <p class="text-slate-400">
                              VersAI, müşteri destek ekibinizi güçlendiren akıllı chatbot çözümü sunar. E-ticaret sipariş takibi, turizm rezervasyon soruları veya emlak portföy tanıtımları için optimize edilmiştir.
                          </p>
                          <ul class="list-none text-slate-400 mt-4">
                              <li class="mb-2 flex items-center">
                                  <i data-feather="check-circle" class="text-amber-400 h-5 w-5 me-2"></i>
                                  Embed.js ile Kolay Entegrasyon
                              </li>
                              <li class="mb-2 flex items-center">
                                  <i data-feather="check-circle" class="text-amber-400 h-5 w-5 me-2"></i>
                                  Chrome Extension ile Hızlı Event Takibi
                              </li>
                              <li class="mb-2 flex items-center">
                                  <i data-feather="check-circle" class="text-amber-400 h-5 w-5 me-2"></i>
                                  AI Eğitimi için Token Bazlı Sistem
                              </li>
                          </ul>
                          

                    
                    </div>
                </div><!--end grid-->
            </div><!--end container-->

            <div class="container relative md:mt-24 mt-16">
                <div class="grid grid-cols-1 pb-6 text-center">
                    <h3 class="mb-4 md:text-3xl md:leading-normal text-2xl leading-normal font-semibold">The right plans, <br> <span class="bg-gradient-to-br from-amber-400 to-fuchsia-600 text-transparent bg-clip-text">for the right price</span></h3>

                    <p class="text-slate-400 max-w-xl mx-auto">Artificial intelligence makes it fast easy to create content for your blog, social media, website, and more!</p>
                </div><!--end grid-->

                <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 mt-6 gap-6">
                    <div class="relative overflow-hidden rounded-md shadow dark:shadow-gray-800">
                        <div class="p-6">
                            <h5 class="text-2xl leading-normal font-semibold">Free</h5>
                            <p class="text-slate-400 mt-2">For anyone to try AI video creation</p>
                            <div class="flex mt-4">
                                <span class="text-lg font-semibold">$</span>
                                <span class="text-5xl font-semibold mb-0 ms-1">0</span>
                            </div>
                            <p class="text-slate-400 uppercase text-xs">per month</p>

                            <div class="mt-6">
                                <a href="" class="py-2 px-5 inline-block font-semibold tracking-wide border align-middle duration-500 text-base text-center bg-amber-400/5 hover:bg-amber-400 rounded border-amber-400/10 hover:border-amber-400 text-amber-400 hover:text-white">Try For Free</a>
    
                                <p class="text-slate-400 text-sm mt-3">No credit card required. Free 14-days trial</p>
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 dark:bg-slate-800">
                            <ul class="list-none text-slate-400">
                               <li class="font-semibold text-slate-900 dark:text-white text-sm uppercase">Features:</li>
                               
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">10 mins/wk</span> of AI generation</li>
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">10 GB</span> storage</li>
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">4 exports/wk</span> with invideo logo</li>
                                <li class="flex items-center mt-2 text-slate-400"><i data-feather="x" class="h-[18px] w-[18px] me-2"></i> 2.5M+ standard media</li>
                                <li class="flex items-center mt-2 text-slate-400"><i data-feather="x" class="h-[18px] w-[18px] me-2"></i> iStock</li>
                            </ul>
                        </div>
                    </div><!--end content-->

                    <div class="relative overflow-hidden rounded-md shadow dark:shadow-gray-800">
                        <div class="p-6">
                            <h5 class="text-2xl leading-normal font-semibold">Business</h5>
                            <p class="text-slate-400 mt-2">For creators starting their journey</p>
                            
                            <div class="relative">
                                <div class="flex mt-4">
                                    <span class="text-lg font-semibold">$</span>
                                    <span class="">
                                        <input type="hidden" id="business-amount" class="form-control">
                                        <p class="text-5xl font-semibold mb-0 ms-1" id="busi-amt"></p>
                                        <p class="text-slate-400 uppercase text-xs">per month</p>
                                    </span>
                                </div>
    
                                <div class="relative mt-4">
                                    <label for="business-price" class="form-label"></label>
                                    <input id="business-price" type="range" value="20" min="20" max="200" class="w-full h-1 bg-gray-50 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer">
                                    <span class="font-semibold text-lg absolute end-0 -top-5">
                                        <input type="hidden" id="business-update" class="form-control">
                                        <span class=""></span>
                                        <p class="inline-block" id="busi-update"></p>
                                        <span>X</span>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="" class="py-2 px-5 inline-block font-semibold tracking-wide border align-middle duration-500 text-base text-center bg-amber-400 hover:bg-amber-500 border-amber-400 hover:border-amber-500 text-white rounded">Subscribe Now</a>
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 dark:bg-slate-800">
                            <ul class="list-none text-slate-400">
                               <li class="font-semibold text-slate-900 dark:text-white text-sm uppercase">Features:</li>
                               
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">50 mins/mo</span> of AI generation</li>
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">100 GB</span> storage</li>
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">Unlimited</span> exports</li>
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> Upto <span class="text-slate-900 dark:text-white mx-1 font-semibold">1</span> user</li>
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">40/mo</span> iStock</li>
                            </ul>
                        </div>
                    </div><!--end content-->

                    <div class="relative overflow-hidden rounded-md shadow dark:shadow-gray-800">
                        <div class="p-6">
                            <h5 class="text-2xl leading-normal font-semibold">Professional</h5>
                            <p class="text-slate-400 mt-2">For growing & established creators</p>

                            <div class="relative">
                                <div class="flex mt-4">
                                    <span class="text-lg font-semibold">$</span>
                                    <span class="">
                                        <input type="hidden" id="professional-amount" class="form-control">
                                        <p class="text-5xl font-semibold mb-0 ms-1" id="pro-amt"></p>
                                        <p class="text-slate-400 uppercase text-xs">per month</p>
                                    </span>
                                </div>
    
                                <div class="relative mt-4">
                                    <label for="professional-price" class="form-label"></label>
                                    <input id="professional-price" type="range" value="40" min="40" max="400" class="w-full h-1 bg-gray-50 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer">
                                    <span class="font-semibold text-lg absolute end-0 -top-5">
                                        <input type="hidden" id="professional-update" class="form-control">
                                        <span class=""></span>
                                        <p class="inline-block" id="pro-update"></p>
                                        <span>X</span>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-4">
                                <a href="" class="py-2 px-5 inline-block font-semibold tracking-wide border align-middle duration-500 text-base text-center bg-amber-400 hover:bg-amber-500 border-amber-400 hover:border-amber-500 text-white rounded">Buy Now</a>
                            </div>
                        </div>

                        <div class="p-6 bg-gray-50 dark:bg-slate-800">
                            <ul class="list-none text-slate-400">
                                <li class="font-semibold text-slate-900 dark:text-white text-sm uppercase">Features:</li>
                               
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">200 mins/mo</span> of AI generation</li>
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">400 GB</span> storage</li>
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">Unlimited</span> exports</li>
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> Upto <span class="text-slate-900 dark:text-white mx-1 font-semibold">1</span> user</li>
                                <li class="flex items-center mt-2"><i data-feather="check-circle" class="text-green-600 h-[18px] w-[18px] me-2"></i> <span class="text-slate-900 dark:text-white me-1 font-semibold">160/mo</span> iStock</li>
                            </ul>
                        </div>
                    </div><!--end content-->
                </div><!--end grid-->
            </div><!--end container-->

            <div class="container relative md:mt-24 mt-16">
                <div class="grid lg:grid-cols-12 md:grid-cols-2 grid-cols-1 items-center md:gap-[30px]">
                    <div class="lg:col-span-4 md:mb-0 mb-8">
                        <h3 class="mb-4 md:text-3xl md:leading-normal text-2xl leading-normal font-semibold">Have a question?</h3>

                        <p class="text-slate-400 max-w-xl mx-auto mb-6">Artificial intelligence makes it fast easy to create content for your blog, social media, website, and more!</p>

                        <a href="" class="py-2 px-5 inline-block font-semibold tracking-wide border align-middle duration-500 text-base text-center bg-transparent hover:bg-amber-400 border-gray-100 dark:border-gray-800 hover:border-amber-400 dark:hover:border-amber-400 text-slate-900 dark:text-white hover:text-white rounded-md">Contact Us</a>
                    </div>

                    <div class="lg:col-span-8 md:mt-0 mt-8" id="accordion-collapse" data-accordion="collapse">
                        <div class="relative shadow dark:shadow-gray-800 rounded-md overflow-hidden">
                            <h2 class="text-base font-semibold" id="accordion-collapse-heading-1">
                                <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start" data-accordion-target="#accordion-collapse-body-1" aria-expanded="true" aria-controls="accordion-collapse-body-1">
                                    <span>How does it generate responses?</span>
                                    <svg data-accordion-icon class="w-4 h-4 rotate-180 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </h2>
                            <div id="accordion-collapse-body-1" class="hidden" aria-labelledby="accordion-collapse-heading-1">
                                <div class="p-5">
                                    <p class="text-slate-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                </div>
                            </div>
                        </div>
    
                        <div class="relative shadow dark:shadow-gray-800 rounded-md overflow-hidden mt-4">
                            <h2 class="text-base font-semibold" id="accordion-collapse-heading-2">
                                <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start" data-accordion-target="#accordion-collapse-body-2" aria-expanded="false" aria-controls="accordion-collapse-body-2">
                                    <span>Is AI copywriting more cost-effective than hiring human writers?</span>
                                    <svg data-accordion-icon class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </h2>
                            <div id="accordion-collapse-body-2" class="hidden" aria-labelledby="accordion-collapse-heading-2">
                                <div class="p-5">
                                    <p class="text-slate-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                </div>
                            </div>
                        </div>
    
                        <div class="relative shadow dark:shadow-gray-800 rounded-md overflow-hidden mt-4">
                            <h2 class="text-base font-semibold" id="accordion-collapse-heading-3">
                                <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start" data-accordion-target="#accordion-collapse-body-3" aria-expanded="false" aria-controls="accordion-collapse-body-3">
                                    <span>Can AI copywriting be customized to my brand and audience?</span>
                                    <svg data-accordion-icon class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </h2>
                            <div id="accordion-collapse-body-3" class="hidden" aria-labelledby="accordion-collapse-heading-3">
                                <div class="p-5">
                                    <p class="text-slate-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="relative shadow dark:shadow-gray-800 rounded-md overflow-hidden mt-4">
                            <h2 class="text-base font-semibold" id="accordion-collapse-heading-4">
                                <button type="button" class="flex justify-between items-center p-5 w-full font-medium text-start" data-accordion-target="#accordion-collapse-body-4" aria-expanded="false" aria-controls="accordion-collapse-body-4">
                                    <span>What kind of support is available for AI copywriting tools?</span>
                                    <svg data-accordion-icon class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </h2>
                            <div id="accordion-collapse-body-4" class="hidden" aria-labelledby="accordion-collapse-heading-4">
                                <div class="p-5">
                                    <p class="text-slate-400 dark:text-gray-400">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.</p>
                                </div>
                            </div>
                        </div>
                    </div><!--end grid-->
                </div><!--end grid-->
            </div><!--end container-->

            <div class="container relative md:mt-24 mt-16">
                <div class="grid grid-cols-1 pb-6 text-center">
                    <h3 class="mb-4 md:text-3xl md:leading-normal text-2xl leading-normal font-semibold">Latest News</h3>

                    <p class="text-slate-400 max-w-xl mx-auto">Artificial intelligence makes it fast easy to create content for your blog, social media, website, and more!</p>
                </div><!--end grid-->

                <div class="grid lg:grid-cols-3 md:grid-cols-2 grid-cols-1 mt-6 gap-6">
                    <div class="relative bg-white dark:bg-slate-900 p-4 rounded-md shadow dark:shadow-gray-700">
                        <img src="{{ asset('landingpage/assets/images/blog/1.jpg') }}" class="rounded-md shadow dark:shadow-gray-700" alt="">
                        <div class="pt-4">
                            <div class="flex justify-between items-center">
                                <div class="space-x-1">
                                    <a href="" class="bg-amber-400/10 text-amber-500 dark:text-amber-400 text-[12px] font-semibold px-2.5 py-0.5 rounded h-5">AI</a>
                                    <a href="" class="bg-amber-400/10 text-amber-500 dark:text-amber-400 text-[12px] font-semibold px-2.5 py-0.5 rounded h-5">Marketing</a>
                                </div>

                                <span class="flex items-center"><i data-feather="clock" class="h-4 w-4"></i> <span class="ms-1 text-slate-400">5 min read</span></span>
                            </div>

                            <div class="mt-5">
                                <a href="blog-detail.html" class="text-lg font-semibold hover:text-amber-400">What is Artificial Intelligence and How Does AI Work For Human</a>
                            </div>

                            <div class="mt-5 flex justify-between items-center">
                                <span class="flex items-center">
                                    <img src="{{ asset('landingpage/assets/images/client/01.jpg') }}" class="h-7 w-7 rounded-full" alt="">
                                    <a href="" class="ms-1 text-slate-400 hover:text-amber-400">Calvin Carlo</a>
                                </span>

                                <span class="flex items-center"><i data-feather="calendar" class="h-4 w-4"></i> <span class="ms-1 text-slate-400">August 24, 2023</span></span>
                            </div>
                        </div>
                    </div><!--end blog content-->
                    
                    <div class="relative bg-white dark:bg-slate-900 p-4 rounded-md shadow dark:shadow-gray-700">
                        <img src="{{ asset('landingpage/assets/images/blog/2.jpg') }}" class="rounded-md shadow dark:shadow-gray-700" alt="">
                        <div class="pt-4">
                            <div class="flex justify-between items-center">
                                <div class="space-x-1">
                                    <a href="" class="bg-amber-400/10 text-amber-500 dark:text-amber-400 text-[12px] font-semibold px-2.5 py-0.5 rounded h-5">AI</a>
                                    <a href="" class="bg-amber-400/10 text-amber-500 dark:text-amber-400 text-[12px] font-semibold px-2.5 py-0.5 rounded h-5">Marketing</a>
                                </div>

                                <span class="flex items-center"><i data-feather="clock" class="h-4 w-4"></i> <span class="ms-1 text-slate-400">5 min read</span></span>
                            </div>

                            <div class="mt-5">
                                <a href="blog-detail.html" class="text-lg font-semibold hover:text-amber-400">Lignin and the future circular make sssay form AI support system</a>
                            </div>

                            <div class="mt-5 flex justify-between items-center">
                                <span class="flex items-center">
                                    <img src="{{ asset('landingpage/assets/images/client/01.jpg') }}" class="h-7 w-7 rounded-full" alt="">
                                    <a href="" class="ms-1 text-slate-400 hover:text-amber-400">Calvin Carlo</a>
                                </span>

                                <span class="flex items-center"><i data-feather="calendar" class="h-4 w-4"></i> <span class="ms-1 text-slate-400">August 24, 2023</span></span>
                            </div>
                        </div>
                    </div><!--end blog content-->

                    <div class="relative bg-white dark:bg-slate-900 p-4 rounded-md shadow dark:shadow-gray-700">
                        <img src="{{ asset('landingpage/assets/images/blog/3.jpg') }}" class="rounded-md shadow dark:shadow-gray-700" alt="">
                        <div class="pt-4">
                            <div class="flex justify-between items-center">
                                <div class="space-x-1">
                                    <a href="" class="bg-amber-400/10 text-amber-500 dark:text-amber-400 text-[12px] font-semibold px-2.5 py-0.5 rounded h-5">AI</a>
                                    <a href="" class="bg-amber-400/10 text-amber-500 dark:text-amber-400 text-[12px] font-semibold px-2.5 py-0.5 rounded h-5">Marketing</a>
                                </div>

                                <span class="flex items-center"><i data-feather="clock" class="h-4 w-4"></i> <span class="ms-1 text-slate-400">5 min read</span></span>
                            </div>

                            <div class="mt-5">
                                <a href="blog-detail.html" class="text-lg font-semibold hover:text-amber-400">Research Operations vs Research Is Always Essay On MasAI Theme</a>
                            </div>

                            <div class="mt-5 flex justify-between items-center">
                                <span class="flex items-center">
                                    <img src="{{ asset('landingpage/assets/images/client/01.jpg') }}" class="h-7 w-7 rounded-full" alt="">
                                    <a href="" class="ms-1 text-slate-400 hover:text-amber-400">Calvin Carlo</a>
                                </span>

                                <span class="flex items-center"><i data-feather="calendar" class="h-4 w-4"></i> <span class="ms-1 text-slate-400">August 24, 2023</span></span>
                            </div>
                        </div>
                    </div><!--end blog content-->
                </div><!--end grid-->
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
                                <a href="" class="py-2 px-5 inline-block font-semibold tracking-wide border align-middle duration-500 text-base text-center bg-transparent hover:bg-amber-400 border-gray-800 dark:border-slate-800 hover:border-amber-400 dark:hover:border-amber-400 text-white rounded-md">Join Now!</a>
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
                                    <img src="{{ asset('landingpage/assets/images/logo-light.png') }}" class="mx-auto md:me-auto md:ms-0" alt="">
                                </a>
                            </div>

                            <ul class="list-none footer-list md:text-end text-center mt-6 md:mt-0">
                                <li class="inline"><a href="https://1.envato.market/mortalai" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="shopping-cart" class="h-4 w-4 align-middle" title="Buy Now"></i></a></li>
                                <li class="inline"><a href="https://dribbble.com/shreethemes" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="dribbble" class="h-4 w-4 align-middle" title="dribbble"></i></a></li>
                                <li class="inline"><a href="http://linkedin.com/company/shreethemes" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="linkedin" class="h-4 w-4 align-middle" title="Linkedin"></i></a></li>
                                <li class="inline"><a href="https://www.facebook.com/shreethemes" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="facebook" class="h-4 w-4 align-middle" title="facebook"></i></a></li>
                                <li class="inline"><a href="https://www.instagram.com/shreethemes/" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="instagram" class="h-4 w-4 align-middle" title="instagram"></i></a></li>
                                <li class="inline"><a href="https://twitter.com/shreethemes" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="twitter" class="h-4 w-4 align-middle" title="twitter"></i></a></li>
                                <li class="inline"><a href="mailto:support@shreethemes.in" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="mail" class="h-4 w-4 align-middle" title="email"></i></a></li>
                                <li class="inline"><a href="https://forms.gle/QkTueCikDGqJnbky9" target="_blank" class="h-8 w-8 inline-flex items-center justify-center tracking-wide align-middle duration-500 text-base text-center border border-gray-800 dark:border-slate-800 rounded-md hover:border-amber-400 dark:hover:border-amber-400 hover:bg-amber-400 dark:hover:bg-amber-400 text-slate-300 hover:text-white"><i data-feather="file-text" class="h-4 w-4 align-middle" title="customization"></i></a></li>
                            </ul><!--end icon-->
                        </div><!--end grid-->
                    </div>
                </div><!--end grid-->
            </div><!--end container-->

            <div class="py-[30px] px-0 border-t border-gray-800 dark:border-slate-800">
                <div class="container relative text-center">
                    <div class="grid grid-cols-1">
                        <div class="text-center">
                            <p class="text-gray-400">© <script>document.write(new Date().getFullYear())</script> Mortal.Ai. Design with <i class="mdi mdi-heart text-orange-700"></i> by <a href="https://shreethemes.in/" target="_blank" class="text-reset">Shreethemes</a>.</p>
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
        <script src="{{ asset('landingpage/assets/libs/tobii/js/tobii.min.js') }}"></script>
        <script src="{{ asset('landingpage/assets/libs/feather-icons/feather.min.js') }}"></script>
        <script src="{{ asset('landingpage/assets/js/plugins.init.js') }}"></script>
        <!-- JAVASCRIPTS -->
    </body>
</html>