<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FuelCab - Premium Multi-Vendor B2B Fuel Delivery Platform</title>
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primaryGreen: '#155c32',
                        secondaryGreen: '#33b248',
                        darkText: '#1A1A1A',
                        bodyText: '#555555',
                        bgLight: '#FAFBFA',
                        sectionBg: '#F4F8F5',
                        borderLight: '#E7ECE8',
                        cardBg: '#FFFFFF',
                        success: '#3CB371',
                        warning: '#FFB400',
                        darkGreen: '#0d361d',
                        footerBg: '#0f1712'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    letterSpacing: {
                        tight: '-0.015em',
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.015em;
            background-color: #FAFBFA;
            color: #1A1A1A;
            overflow-x: hidden;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Glassmorphism utility */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        
        .glass-card-dark {
            background: rgba(13, 54, 29, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Animations */
        @keyframes floatTruck {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        @keyframes floatCard {
            0% { transform: translateY(-15px) translateX(-5px); }
            50% { transform: translateY(-5px) translateX(5px); }
            100% { transform: translateY(-15px) translateX(-5px); }
        }

        .animate-float-truck {
            animation: floatTruck 6s ease-in-out infinite;
        }

        .animate-float-card {
            animation: floatCard 5s ease-in-out infinite;
        }

        /* Sticky Nav Blur */
        .navbar-blur {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        
        /* Dotted timeline connection line */
        .timeline-dotted {
            background-image: radial-gradient(circle, #33b248 1.5px, transparent 1.5px);
            background-size: 12px 12px;
            background-repeat: repeat-x;
        }
    </style>
</head>
<body class="antialiased">

    <!-- 1. TOP ANNOUNCEMENT BAR -->
    <div class="bg-darkGreen text-white h-[42px] flex items-center px-4 md:px-8 text-xs md:text-sm font-medium z-50 relative">
        <div class="max-w-[1440px] w-full mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span class="bg-secondaryGreen/20 text-secondaryGreen px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider">Alert</span>
                <span>Minimum order quantity is 100 Liters. Reliable fuel delivery for your business.</span>
            </div>
            <div class="hidden md:flex items-center gap-6 text-gray-300">
                <a href="#about" class="hover:text-white transition">About Us</a>
                <a href="#careers" class="hover:text-white transition">Careers</a>
                <a href="#contact" class="hover:text-white transition">Contact Us</a>
            </div>
        </div>
    </div>

    <!-- 2. STICKY NAVBAR -->
    <nav id="navbar" class="w-full h-[82px] border-b border-borderLight sticky top-0 z-40 transition-all duration-300 bg-white">
        <div class="max-w-[1440px] h-full mx-auto px-4 md:px-8 flex justify-between items-center">
            <!-- Logo Left -->
            <a href="#" class="flex items-center gap-2">
                <div class="bg-primaryGreen text-white p-2 rounded-lg">
                    <i data-lucide="droplet" class="w-6 h-6 fill-secondaryGreen stroke-primaryGreen"></i>
                </div>
                <span class="text-2xl font-bold tracking-tight text-darkText">Fuel<span class="text-primaryGreen">Cab</span></span>
            </a>

            <!-- Menu Center -->
            <div class="hidden lg:flex items-center gap-8 font-medium text-bodyText">
                <a href="#" class="hover:text-primaryGreen transition text-primaryGreen">Home</a>
                <a href="#about" class="hover:text-primaryGreen transition">About</a>
                <a href="#products" class="hover:text-primaryGreen transition">Products</a>
                <a href="#how-it-works" class="hover:text-primaryGreen transition">How It Works</a>
                <a href="#industries" class="hover:text-primaryGreen transition">Industries</a>
                <a href="#partner" class="hover:text-primaryGreen transition">Partner With Us</a>
                <a href="#faqs" class="hover:text-primaryGreen transition">FAQs</a>
            </div>

            <!-- Right Side Actions -->
            <div class="hidden md:flex items-center gap-4">
                <a href="/admin/login" class="px-5 py-2.5 border border-borderLight rounded-xl font-semibold hover:border-primaryGreen text-darkText hover:text-primaryGreen transition text-sm">Login</a>
                <a href="/admin/register" class="px-6 py-2.5 bg-primaryGreen text-white rounded-xl font-semibold hover:bg-darkGreen hover:shadow-lg transition text-sm">Register</a>
            </div>

            <!-- Mobile Hamburger Button -->
            <button id="mobile-menu-btn" class="lg:hidden p-2 text-darkText hover:text-primaryGreen transition">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Mobile Drawer Menu -->
        <div id="mobile-menu" class="hidden absolute top-[82px] left-0 w-full bg-white border-b border-borderLight p-6 flex flex-col gap-4 shadow-xl z-50">
            <a href="#" class="font-medium text-darkText hover:text-primaryGreen py-2">Home</a>
            <a href="#about" class="font-medium text-darkText hover:text-primaryGreen py-2">About</a>
            <a href="#products" class="font-medium text-darkText hover:text-primaryGreen py-2">Products</a>
            <a href="#how-it-works" class="font-medium text-darkText hover:text-primaryGreen py-2">How It Works</a>
            <a href="#industries" class="font-medium text-darkText hover:text-primaryGreen py-2">Industries</a>
            <a href="#partner" class="font-medium text-darkText hover:text-primaryGreen py-2">Partner With Us</a>
            <a href="#faqs" class="font-medium text-darkText hover:text-primaryGreen py-2">FAQs</a>
            <hr class="border-borderLight">
            <div class="flex flex-col gap-2 pt-2">
                <a href="/admin/login" class="w-full py-3 text-center border border-borderLight rounded-xl font-semibold text-darkText">Login</a>
                <a href="/admin/register" class="w-full py-3 text-center bg-primaryGreen text-white rounded-xl font-semibold">Register</a>
            </div>
        </div>
    </nav>

    <!-- 3. HERO SECTION -->
    <section class="relative pt-12 pb-24 md:py-32 overflow-hidden bg-gradient-to-br from-bgLight via-bgLight to-sectionBg">
        <!-- Abstract Background Lines/Gradients -->
        <div class="absolute inset-0 z-0 pointer-events-none opacity-40">
            <div class="absolute top-1/4 right-1/4 w-[600px] h-[600px] bg-secondaryGreen/5 rounded-full filter blur-3xl"></div>
            <div class="absolute -bottom-1/4 left-1/4 w-[500px] h-[500px] bg-primaryGreen/5 rounded-full filter blur-3xl"></div>
            <svg class="absolute right-0 top-1/2 -translate-y-1/2 w-1/2 h-full stroke-gray-200/50" fill="none" viewBox="0 0 400 400">
                <circle cx="200" cy="200" r="180" stroke-width="1" stroke-dasharray="4 4" />
                <circle cx="200" cy="200" r="140" stroke-width="1" />
                <circle cx="200" cy="200" r="100" stroke-width="1" stroke-dasharray="2 2" />
            </svg>
        </div>

        <div class="max-w-[1440px] mx-auto px-4 md:px-8 relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <!-- Left Side 45% (5 cols) -->
            <div class="lg:col-span-5 flex flex-col items-start text-left">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-primaryGreen/10 text-primaryGreen rounded-full text-xs font-semibold uppercase tracking-wider mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-primaryGreen animate-pulse"></span>
                    B2B Fuel Delivery
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-darkText leading-[1.1] mb-6">
                    Fueling Business, <br><span class="text-primaryGreen">Powering Progress</span>
                </h1>
                <p class="text-bodyText text-base md:text-lg max-w-[500px] mb-8 leading-relaxed">
                    FuelCab is a multi-vendor fuel delivery platform for businesses. Order Diesel and other fuels in minimum 100 liters and get it delivered fast, safely & reliably.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto mb-12">
                    <a href="#products" class="px-8 py-4 bg-primaryGreen text-white rounded-xl font-semibold hover:bg-darkGreen hover:shadow-xl transition duration-300 flex items-center justify-center gap-2 group">
                        Order Diesel Now
                        <i data-lucide="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition"></i>
                    </a>
                    <a href="#partner" class="px-8 py-4 border border-primaryGreen text-primaryGreen rounded-xl font-semibold hover:bg-primaryGreen/5 transition duration-300 flex items-center justify-center">
                        Become a Vendor
                    </a>
                </div>
                
                <!-- 3 Feature Tags -->
                <div class="grid grid-cols-3 gap-4 border-t border-borderLight pt-8 w-full max-w-[520px]">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 text-primaryGreen mb-1">
                            <i data-lucide="package" class="w-5 h-5"></i>
                            <span class="text-xs font-bold uppercase tracking-wider text-darkText">Min. Order</span>
                        </div>
                        <span class="text-sm text-bodyText">100 Liters Only</span>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 text-primaryGreen mb-1">
                            <i data-lucide="truck" class="w-5 h-5"></i>
                            <span class="text-xs font-bold uppercase tracking-wider text-darkText">Fast Delivery</span>
                        </div>
                        <span class="text-sm text-bodyText">On-Time, Safely</span>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 text-primaryGreen mb-1">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                            <span class="text-xs font-bold uppercase tracking-wider text-darkText">Quality</span>
                        </div>
                        <span class="text-sm text-bodyText">Assured Fuel</span>
                    </div>
                </div>
            </div>

            <!-- Right Side 55% (7 cols) -->
            <div class="lg:col-span-7 relative flex justify-center items-center mt-12 lg:mt-0">
                <!-- Main Tanker Image Wrapper -->
                <div class="relative w-full max-w-[620px] z-10 animate-float-truck">
                    <img src="https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&q=80&w=800" 
                         alt="FuelCab B2B Delivery Truck" 
                         class="rounded-[24px] shadow-2xl object-cover border border-white/80">
                         
                    <!-- Overlay Branding Logo -->
                    <div class="absolute bottom-6 left-6 bg-white/95 px-4 py-2 rounded-xl shadow-lg flex items-center gap-2 border border-borderLight">
                        <div class="bg-primaryGreen text-white p-1 rounded">
                            <i data-lucide="droplet" class="w-4 h-4 fill-secondaryGreen stroke-primaryGreen"></i>
                        </div>
                        <span class="font-bold text-sm text-darkText">FuelCab Delivery</span>
                    </div>
                </div>

                <!-- Floating Glassmorphism Fuel Card -->
                <div class="absolute top-10 right-4 lg:right-10 z-20 w-[240px] p-5 rounded-[20px] glass-card-dark text-white shadow-2xl animate-float-card">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex items-center gap-2">
                            <div class="bg-warning/20 p-2 rounded-lg">
                                <i data-lucide="flame" class="w-6 h-6 text-warning fill-warning"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-300 block uppercase font-bold tracking-wider">Our Top Priority</span>
                                <h4 class="font-bold text-base tracking-tight">DIESEL</h4>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-200 leading-relaxed mb-4">
                        High quality diesel for uninterrupted performance of your business.
                    </p>
                    <a href="#products" class="flex items-center justify-between text-xs font-semibold text-secondaryGreen hover:text-white transition group">
                        <span>Order Diesel Now</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 group-hover:translate-x-1 transition"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. SECTION 2: PRODUCT RANGE -->
    <section id="products" class="py-24 bg-white border-t border-borderLight">
        <div class="max-w-[1440px] mx-auto px-4 md:px-8">
            <div class="text-center max-w-[650px] mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-secondaryGreen mb-3 block">Our Fuel Range</span>
                <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-darkText mb-4">Powering Every Business Need</h2>
                <p class="text-bodyText text-sm">While Diesel is our top priority, we also deliver a wide range of fuels to keep your business moving.</p>
            </div>

            <!-- 5 Product Cards Horizontal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                
                <!-- Diesel Card (Highlighted) -->
                <div class="bg-darkGreen text-white rounded-[20px] p-8 shadow-xl flex flex-col justify-between h-[360px] border border-primaryGreen hover:-translate-y-2 transition-all duration-300 group">
                    <div>
                        <div class="flex justify-between items-start mb-6">
                            <div class="bg-warning/20 p-3 rounded-xl">
                                <i data-lucide="droplet" class="w-8 h-8 text-warning fill-warning"></i>
                            </div>
                            <span class="bg-warning text-darkText text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Top Priority</span>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Diesel</h3>
                        <p class="text-sm text-gray-200 leading-relaxed">
                            High performance diesel for industrial, commercial and logistics operations.
                        </p>
                    </div>
                    <a href="/admin/login" class="bg-white text-darkGreen py-3 px-4 rounded-xl font-semibold flex items-center justify-center gap-2 hover:bg-secondaryGreen hover:text-white transition group-hover:shadow-lg">
                        Order Diesel
                        <i data-lucide="arrow-right" class="w-4 h-4 transition group-hover:translate-x-1"></i>
                    </a>
                </div>

                <!-- Petrol Card -->
                <div class="bg-white text-darkText rounded-[20px] p-8 shadow-sm hover:shadow-xl hover:border-secondaryGreen border border-borderLight flex flex-col justify-between h-[360px] hover:-translate-y-2 transition-all duration-300 group">
                    <div>
                        <div class="flex justify-between items-start mb-6">
                            <div class="bg-primaryGreen/10 p-3 rounded-xl">
                                <i data-lucide="droplet" class="w-8 h-8 text-primaryGreen fill-primaryGreen"></i>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Petrol</h3>
                        <p class="text-sm text-bodyText leading-relaxed">
                            Quality petrol for generators, equipment and light commercial vehicles.
                        </p>
                    </div>
                    <a href="/admin/login" class="border border-borderLight text-darkText hover:border-primaryGreen py-3 px-4 rounded-xl font-semibold flex items-center justify-center gap-2 hover:bg-primaryGreen/5 transition">
                        Order Now
                        <i data-lucide="arrow-right" class="w-4 h-4 transition group-hover:translate-x-1"></i>
                    </a>
                </div>

                <!-- HSD Card -->
                <div class="bg-white text-darkText rounded-[20px] p-8 shadow-sm hover:shadow-xl hover:border-secondaryGreen border border-borderLight flex flex-col justify-between h-[360px] hover:-translate-y-2 transition-all duration-300 group">
                    <div>
                        <div class="flex justify-between items-start mb-6">
                            <div class="bg-secondaryGreen/10 p-3 rounded-xl">
                                <i data-lucide="droplets" class="w-8 h-8 text-secondaryGreen fill-secondaryGreen"></i>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold mb-3">HSD</h3>
                        <p class="text-sm text-bodyText leading-relaxed">
                            High Speed Diesel for heavy construction vehicles and turbines.
                        </p>
                    </div>
                    <a href="/admin/login" class="border border-borderLight text-darkText hover:border-primaryGreen py-3 px-4 rounded-xl font-semibold flex items-center justify-center gap-2 hover:bg-primaryGreen/5 transition">
                        Order Now
                        <i data-lucide="arrow-right" class="w-4 h-4 transition group-hover:translate-x-1"></i>
                    </a>
                </div>

                <!-- Lubricants Card -->
                <div class="bg-white text-darkText rounded-[20px] p-8 shadow-sm hover:shadow-xl hover:border-secondaryGreen border border-borderLight flex flex-col justify-between h-[360px] hover:-translate-y-2 transition-all duration-300 group">
                    <div>
                        <div class="flex justify-between items-start mb-6">
                            <div class="bg-primaryGreen/10 p-3 rounded-xl">
                                <i data-lucide="cog" class="w-8 h-8 text-primaryGreen"></i>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Lubricants</h3>
                        <p class="text-sm text-bodyText leading-relaxed">
                            Premium oils & lubricants to keep your engines running smoothly.
                        </p>
                    </div>
                    <a href="/admin/login" class="border border-borderLight text-darkText hover:border-primaryGreen py-3 px-4 rounded-xl font-semibold flex items-center justify-center gap-2 hover:bg-primaryGreen/5 transition">
                        Order Now
                        <i data-lucide="arrow-right" class="w-4 h-4 transition group-hover:translate-x-1"></i>
                    </a>
                </div>

                <!-- Others Card -->
                <div class="bg-white text-darkText rounded-[20px] p-8 shadow-sm hover:shadow-xl hover:border-secondaryGreen border border-borderLight flex flex-col justify-between h-[360px] hover:-translate-y-2 transition-all duration-300 group">
                    <div>
                        <div class="flex justify-between items-start mb-6">
                            <div class="bg-secondaryGreen/10 p-3 rounded-xl">
                                <i data-lucide="sparkles" class="w-8 h-8 text-secondaryGreen"></i>
                            </div>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Others</h3>
                        <p class="text-sm text-bodyText leading-relaxed">
                            Explore our range of other fuel products tailored for your specific needs.
                        </p>
                    </div>
                    <a href="/admin/login" class="border border-borderLight text-darkText hover:border-primaryGreen py-3 px-4 rounded-xl font-semibold flex items-center justify-center gap-2 hover:bg-primaryGreen/5 transition">
                        Order Now
                        <i data-lucide="arrow-right" class="w-4 h-4 transition group-hover:translate-x-1"></i>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- 5. SECTION 3: HOW IT WORKS -->
    <section id="how-it-works" class="py-24 bg-sectionBg border-t border-b border-borderLight">
        <div class="max-w-[1440px] mx-auto px-4 md:px-8">
            <div class="text-center max-w-[650px] mx-auto mb-20">
                <span class="text-xs font-bold uppercase tracking-wider text-primaryGreen mb-3 block">Simple. Fast. Reliable.</span>
                <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-darkText mb-4">Ordering fuel has never been easier.</h2>
            </div>

            <!-- Timeline wrapper -->
            <div class="relative">
                <!-- Connected dotted line (hidden on mobile) -->
                <div class="hidden lg:block absolute top-[45px] left-[12%] right-[12%] h-[2px] timeline-dotted z-0"></div>

                <!-- 4 Steps -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 relative z-10">
                    
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center text-center group">
                        <div class="w-[90px] h-[90px] rounded-full bg-white border-2 border-secondaryGreen flex items-center justify-center shadow-lg transition duration-300 group-hover:scale-110 mb-6 bg-gradient-to-br from-white to-sectionBg">
                            <i data-lucide="clipboard-list" class="w-8 h-8 text-primaryGreen"></i>
                        </div>
                        <span class="text-xs font-bold text-secondaryGreen mb-1">1</span>
                        <h4 class="text-lg font-bold text-darkText mb-2">Place Order</h4>
                        <p class="text-sm text-bodyText max-w-[240px]">
                            Choose your fuel type, enter the quantity (min. 100L) and place your order.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex flex-col items-center text-center group">
                        <div class="w-[90px] h-[90px] rounded-full bg-white border-2 border-secondaryGreen flex items-center justify-center shadow-lg transition duration-300 group-hover:scale-110 mb-6 bg-gradient-to-br from-white to-sectionBg">
                            <i data-lucide="building" class="w-8 h-8 text-primaryGreen"></i>
                        </div>
                        <span class="text-xs font-bold text-secondaryGreen mb-1">2</span>
                        <h4 class="text-lg font-bold text-darkText mb-2">Vendor Confirmation</h4>
                        <p class="text-sm text-bodyText max-w-[240px]">
                            Nearby vendors receive your order and confirm the best offers.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex flex-col items-center text-center group">
                        <div class="w-[90px] h-[90px] rounded-full bg-white border-2 border-secondaryGreen flex items-center justify-center shadow-lg transition duration-300 group-hover:scale-110 mb-6 bg-gradient-to-br from-white to-sectionBg">
                            <i data-lucide="truck" class="w-8 h-8 text-primaryGreen"></i>
                        </div>
                        <span class="text-xs font-bold text-secondaryGreen mb-1">3</span>
                        <h4 class="text-lg font-bold text-darkText mb-2">Fast Delivery</h4>
                        <p class="text-sm text-bodyText max-w-[240px]">
                            Fuel is dispatched and delivered directly to your site.
                        </p>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex flex-col items-center text-center group">
                        <div class="w-[90px] h-[90px] rounded-full bg-white border-2 border-secondaryGreen flex items-center justify-center shadow-lg transition duration-300 group-hover:scale-110 mb-6 bg-gradient-to-br from-white to-sectionBg">
                            <i data-lucide="shield-check" class="w-8 h-8 text-primaryGreen"></i>
                        </div>
                        <span class="text-xs font-bold text-secondaryGreen mb-1">4</span>
                        <h4 class="text-lg font-bold text-darkText mb-2">Quality Assured</h4>
                        <p class="text-sm text-bodyText max-w-[240px]">
                            Quality check performed at delivery site to ensure purity.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- 6. SECTION 4: INDUSTRIES WE SERVE -->
    <section id="industries" class="py-24 bg-white overflow-hidden">
        <div class="max-w-[1440px] mx-auto px-4 md:px-8 grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
            
            <!-- Left Text Column (4 cols) -->
            <div class="lg:col-span-4 text-left">
                <span class="text-xs font-bold uppercase tracking-wider text-secondaryGreen mb-3 block">Industries We Serve</span>
                <h2 class="text-3xl md:text-4xl font-bold tracking-tight text-darkText mb-6 leading-tight">Fueling Every Industry</h2>
                <p class="text-bodyText text-sm mb-8 leading-relaxed">
                    We deliver customized fuel solutions tailored to meet the operational demands of diverse business sectors.
                </p>
                
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center gap-3 text-darkText font-medium">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-primaryGreen"></i> Logistics & Transportation
                    </li>
                    <li class="flex items-center gap-3 text-darkText font-medium">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-primaryGreen"></i> Construction & Infrastructure
                    </li>
                    <li class="flex items-center gap-3 text-darkText font-medium">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-primaryGreen"></i> Factories & Manufacturing
                    </li>
                    <li class="flex items-center gap-3 text-darkText font-medium">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-primaryGreen"></i> Agriculture & Farming
                    </li>
                    <li class="flex items-center gap-3 text-darkText font-medium">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-primaryGreen"></i> Power & Backup Generators
                    </li>
                </ul>

                <a href="/admin/register" class="px-6 py-3 border border-primaryGreen text-primaryGreen rounded-xl font-semibold hover:bg-primaryGreen hover:text-white transition inline-flex items-center gap-2 group">
                    Explore All Industries
                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition"></i>
                </a>
            </div>

            <!-- Right Industry Cards Column (8 cols) -->
            <div class="lg:col-span-8">
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 h-[450px]">
                    
                    <!-- Card 1: Logistics -->
                    <div class="relative rounded-2xl overflow-hidden group h-full shadow-lg">
                        <img src="https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&q=80&w=600" alt="Logistics Truck" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-darkGreen via-darkGreen/45 to-transparent flex flex-col justify-end p-4">
                            <span class="text-white font-bold text-sm">Logistics</span>
                        </div>
                    </div>

                    <!-- Card 2: Construction -->
                    <div class="relative rounded-2xl overflow-hidden group h-full shadow-lg">
                        <img src="https://images.unsplash.com/photo-1578328819058-b69f3a3b0f6b?auto=format&fit=crop&q=80&w=600" alt="Construction Excavator" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-darkGreen via-darkGreen/45 to-transparent flex flex-col justify-end p-4">
                            <span class="text-white font-bold text-sm">Construction</span>
                        </div>
                    </div>

                    <!-- Card 3: Manufacturing -->
                    <div class="relative rounded-2xl overflow-hidden group h-full shadow-lg">
                        <img src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80&w=600" alt="Manufacturing Factory" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-darkGreen via-darkGreen/45 to-transparent flex flex-col justify-end p-4">
                            <span class="text-white font-bold text-sm">Factories</span>
                        </div>
                    </div>

                    <!-- Card 4: Generators -->
                    <div class="relative rounded-2xl overflow-hidden group h-full shadow-lg">
                        <img src="https://images.unsplash.com/photo-1540324155974-7265d7cb6d1b?auto=format&fit=crop&q=80&w=600" alt="Generators / Power" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-darkGreen via-darkGreen/45 to-transparent flex flex-col justify-end p-4">
                            <span class="text-white font-bold text-sm">Power</span>
                        </div>
                    </div>

                    <!-- Card 5: Agriculture -->
                    <div class="relative rounded-2xl overflow-hidden group h-full shadow-lg">
                        <img src="https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&q=80&w=600" alt="Agriculture tractor" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-darkGreen via-darkGreen/45 to-transparent flex flex-col justify-end p-4">
                            <span class="text-white font-bold text-sm">Farming</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- 7. SECTION 5: STATISTICS -->
    <section class="py-12 bg-white">
        <div class="max-w-[1440px] mx-auto px-4 md:px-8">
            <div class="bg-darkGreen text-white rounded-[24px] p-8 md:p-12 shadow-2xl relative overflow-hidden">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center divide-y lg:divide-y-0 lg:divide-x divide-white/10">
                    
                    <div class="flex flex-col justify-center items-center p-4">
                        <i data-lucide="users" class="w-6 h-6 text-secondaryGreen mb-3"></i>
                        <span class="text-3xl md:text-4xl font-extrabold mb-1">500+</span>
                        <span class="text-xs uppercase tracking-wider text-gray-300 font-medium">Active Businesses</span>
                    </div>

                    <div class="flex flex-col justify-center items-center p-4">
                        <i data-lucide="droplet" class="w-6 h-6 text-secondaryGreen mb-3"></i>
                        <span class="text-3xl md:text-4xl font-extrabold mb-1">100K+</span>
                        <span class="text-xs uppercase tracking-wider text-gray-300 font-medium">Liters Delivered Daily</span>
                    </div>

                    <div class="flex flex-col justify-center items-center p-4">
                        <i data-lucide="handshake" class="w-6 h-6 text-secondaryGreen mb-3"></i>
                        <span class="text-3xl md:text-4xl font-extrabold mb-1">50+</span>
                        <span class="text-xs uppercase tracking-wider text-gray-300 font-medium">Delivery Partners</span>
                    </div>

                    <div class="flex flex-col justify-center items-center p-4">
                        <i data-lucide="timer" class="w-6 h-6 text-secondaryGreen mb-3"></i>
                        <span class="text-3xl md:text-4xl font-extrabold mb-1">98%</span>
                        <span class="text-xs uppercase tracking-wider text-gray-300 font-medium">On-Time Delivery</span>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- 8. SECTION 6: CTA WITH MOCKUP -->
    <section class="py-24 bg-white relative">
        <div class="max-w-[1440px] mx-auto px-4 md:px-8">
            <div class="bg-gradient-to-r from-darkGreen to-primaryGreen text-white rounded-[32px] p-8 md:p-16 shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 gap-12 items-center relative">
                
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-5 pointer-events-none">
                    <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />
                    </svg>
                </div>

                <!-- Left Content -->
                <div class="lg:col-span-7 z-10 text-left">
                    <h2 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-4">
                        Ready to Power Your Business?
                    </h2>
                    <p class="text-gray-200 text-sm md:text-base mb-8 max-w-[540px] leading-relaxed">
                        Join FuelCab today and experience reliable multi-vendor fuel delivery tailored for your business. Minimum order 100 liters.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto mb-8">
                        <a href="/admin/login" class="px-8 py-4 bg-white text-darkGreen rounded-xl font-bold hover:bg-secondaryGreen hover:text-white transition duration-300 text-center text-sm shadow-md">
                            Login
                        </a>
                        <a href="/admin/register" class="px-8 py-4 bg-secondaryGreen text-white rounded-xl font-bold hover:bg-white hover:text-darkGreen transition duration-300 text-center text-sm shadow-md">
                            Register Now
                        </a>
                    </div>
                    
                    <!-- App Badges -->
                    <div class="flex items-center gap-4 border-t border-white/10 pt-8 mt-8">
                        <span class="text-xs uppercase font-semibold text-gray-300 tracking-wider">Also Available on</span>
                        <a href="#" class="hover:opacity-80 transition"><img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Google Play Store" class="h-10"></a>
                        <a href="#" class="hover:opacity-80 transition"><img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" alt="App Store" class="h-10"></a>
                    </div>
                </div>

                <!-- Right Mockup Image (5 cols) -->
                <div class="lg:col-span-5 relative flex justify-center z-10">
                    <div class="w-[280px] md:w-[320px] aspect-[9/18.5] bg-[#0A0E0B] rounded-[48px] p-3 shadow-2xl border-[6px] border-[#2A302C] relative overflow-hidden">
                        <!-- Camera Notch -->
                        <div class="absolute top-4 left-1/2 -translate-x-1/2 w-32 h-6 bg-black rounded-full z-30 flex items-center justify-center">
                            <div class="w-3 h-3 rounded-full bg-gray-800"></div>
                        </div>
                        
                        <!-- Internal App UI Mockup -->
                        <div class="w-full h-full bg-[#FAF9F5] rounded-[38px] p-4 flex flex-col justify-between overflow-hidden text-darkText text-left">
                            <div class="pt-6">
                                <div class="flex justify-between items-center mb-6">
                                    <span class="text-xs font-bold text-primaryGreen">FuelCab App</span>
                                    <i data-lucide="bell" class="w-4 h-4 text-gray-500"></i>
                                </div>
                                <div class="bg-white p-4 rounded-2xl shadow-sm border border-borderLight mb-4">
                                    <span class="text-[10px] text-gray-400 block uppercase">Order Status</span>
                                    <h5 class="font-bold text-sm text-darkText">Diesel Dispatched</h5>
                                    <div class="w-full bg-gray-100 h-1.5 rounded-full mt-3 overflow-hidden">
                                        <div class="bg-primaryGreen w-3/4 h-full rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="bg-darkGreen text-white p-4 rounded-2xl shadow-lg">
                                    <span class="text-[9px] uppercase tracking-wider text-gray-300">Quick Actions</span>
                                    <h4 class="font-bold text-sm mb-2">Refuel Instantly</h4>
                                    <button class="w-full py-2 bg-secondaryGreen text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5">
                                        <i data-lucide="navigation" class="w-3.5 h-3.5 fill-white"></i> Track Driver
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- 9. FOOTER -->
    <footer class="bg-footerBg text-gray-300 py-16 border-t border-white/5">
        <div class="max-w-[1440px] mx-auto px-4 md:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12">
            
            <!-- Column 1: Logo & About -->
            <div class="lg:col-span-2 text-left">
                <a href="#" class="flex items-center gap-2 mb-6">
                    <div class="bg-primaryGreen text-white p-2 rounded-lg">
                        <i data-lucide="droplet" class="w-5 h-5 fill-secondaryGreen stroke-primaryGreen"></i>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-white">Fuel<span class="text-secondaryGreen">Cab</span></span>
                </a>
                <p class="text-sm text-gray-400 max-w-[320px] leading-relaxed mb-6">
                    FuelCab is a multi-vendor B2B fuel delivery platform. We ensure fast, safe, reliable, and high-quality fuel delivery for your businesses.
                </p>
                <div class="flex items-center gap-4">
                    <a href="#" class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center hover:bg-primaryGreen transition"><i data-lucide="facebook" class="w-4 h-4 text-white"></i></a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center hover:bg-primaryGreen transition"><i data-lucide="twitter" class="w-4 h-4 text-white"></i></a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center hover:bg-primaryGreen transition"><i data-lucide="linkedin" class="w-4 h-4 text-white"></i></a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center hover:bg-primaryGreen transition"><i data-lucide="instagram" class="w-4 h-4 text-white"></i></a>
                </div>
            </div>

            <!-- Column 2: Company -->
            <div class="text-left">
                <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-6">Company</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition">About Us</a></li>
                    <li><a href="#" class="hover:text-white transition">Careers</a></li>
                    <li><a href="#" class="hover:text-white transition">Blog</a></li>
                    <li><a href="#" class="hover:text-white transition">Contact Us</a></li>
                </ul>
            </div>

            <!-- Column 3: Products -->
            <div class="text-left">
                <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-6">Products</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#products" class="hover:text-white transition">Diesel</a></li>
                    <li><a href="#products" class="hover:text-white transition">Petrol</a></li>
                    <li><a href="#products" class="hover:text-white transition">HSD</a></li>
                    <li><a href="#products" class="hover:text-white transition">Lubricants</a></li>
                    <li><a href="#products" class="hover:text-white transition">Others</a></li>
                </ul>
            </div>

            <!-- Column 4: Support & Legal -->
            <div class="text-left">
                <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-6">Support</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition">FAQs</a></li>
                    <li><a href="#" class="hover:text-white transition">Terms & Conditions</a></li>
                    <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-white transition">Return Policy</a></li>
                </ul>
            </div>

        </div>

        <div class="max-w-[1440px] mx-auto px-4 md:px-8 border-t border-white/5 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-500">
            <span>&copy; 2026 FuelCab. All rights reserved.</span>
            <span>Made with ❤️ for businesses like yours.</span>
        </div>
    </footer>

    <!-- JS Scripts for navbar sticky effect and mobile menu toggle -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Navbar Sticky and Shadow script
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 20) {
                navbar.classList.add('navbar-blur', 'shadow-md');
                navbar.classList.remove('bg-white');
            } else {
                navbar.classList.remove('navbar-blur', 'shadow-md');
                navbar.classList.add('bg-white');
            }
        });

        // Mobile menu toggle
        const menuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        menuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
