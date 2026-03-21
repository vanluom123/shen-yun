<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Phong trà') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $isRegisterFlow = request()->is('register*') || request()->is('login');
        $isAdmin = request()->is('admin*') && session('admin_authed');
        $appTitle = config('app.name', 'Phong trà');
    @endphp

    @if ($isRegisterFlow)
        <body class="rsvp-shell {{ request()->is('login') ? 'rsvp-access' : '' }}">
            <div class="rsvp-shell">
                <div class="rsvp-top">
                    <div class="rsvp-title">{{ $appTitle }}</div>
                    <div class="rsvp-subtitle">Trải nghiệm hành trình âm nhạc &amp; vũ đạo thư giãn cuối tuần</div>
                </div>

                <main class="px-4 pb-12">
                    <div class="rsvp-panel">
                        @if (session('status'))
                            <div class="mb-5 rounded-2xl border border-emerald-400/30 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-100">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-5 rounded-2xl border border-rose-400/30 bg-rose-950/35 px-4 py-3 text-sm text-rose-100">
                                <div class="font-semibold tracking-wide">Vui lòng kiểm tra lại</div>
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{ $slot ?? '' }}
                        @yield('content')
                    </div>
                </main>
            </div>
        </body>
    @elseif ($isAdmin)
        <body class="min-h-dvh bg-background text-on-surface antialiased font-sans">
            <div class="flex min-h-dvh flex-col lg:flex-row relative">
                <!-- Mobile Backdrop -->
                <div id="mobile-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity duration-300 opacity-0 lg:hidden"></div>

                <!-- Sidebar -->
                <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out lg:fixed lg:translate-x-0 w-[280px] lg:w-64 bg-slate-50 border-r border-outline-variant/20 flex flex-col p-4 shadow-xl lg:shadow-none h-dvh">
                    <div class="px-2 py-6 mb-4">
                        <h2 class="text-xl font-bold tracking-tight text-on-surface">{{ $appTitle }}</h2>
                        <p class="text-xs text-on-surface-variant opacity-70 font-medium uppercase tracking-widest mt-1">Admin Space</p>
                    </div>
                    
                    <nav class="flex-1 space-y-1">
                        @include('admin.partials.nav')
                    </nav>

                    <div class="mt-auto pt-4 border-t border-outline-variant/30">
                         <form method="post" action="{{ url('/admin/logout') }}">
                            @csrf
                            <button class="flex w-full items-center gap-3 px-3 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface rounded-xl transition-all group">
                                <span class="material-symbols-outlined text-xl group-hover:scale-110 transition-transform">logout</span>
                                <span>Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </aside>

                <!-- Main Area -->
                <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
                    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-outline-variant/20 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-30">
                        <div class="flex items-center gap-3">
                            <button id="mobile-menu-toggle" class="lg:hidden p-2 -ml-2 text-on-surface-variant hover:text-on-surface focus:outline-none flex flex-col justify-center items-center w-10 h-10 relative">
                                <span class="block w-5 h-0.5 bg-current transition-all duration-300 ease-in-out absolute top-[14px]"></span>
                                <span class="block w-5 h-0.5 bg-current transition-all duration-300 ease-in-out absolute top-[19px]"></span>
                                <span class="block w-5 h-0.5 bg-current transition-all duration-300 ease-in-out absolute bottom-[14px]"></span>
                            </button>
                            <span class="lg:hidden font-bold tracking-tight text-on-surface text-base">THƯ MỜI - ADMIN SPACE</span>
                            <div class="hidden lg:block">
                                <span class="text-sm font-semibold text-on-surface-variant/60">Quản lý</span>
                                <span class="mx-1 text-on-surface-variant/30">/</span>
                                <span class="text-sm font-bold text-on-surface">{{ $title ?? 'Dashboard' }}</span>
                            </div>
                        </div>
                    </header>

                    <main class="flex-1 p-5 sm:p-10">
                        @if (session('status'))
                            <div class="mb-6 rounded-2xl border border-emerald-200/60 bg-emerald-50/90 px-4 py-3 text-emerald-900 shadow-sm">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-6 rounded-2xl border border-rose-200/70 bg-rose-50/90 px-4 py-3 text-rose-900 shadow-sm">
                                <div class="font-bold">Có lỗi xảy ra</div>
                                <ul class="mt-2 list-disc pl-5 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{ $slot ?? '' }}
                        @yield('content')
                    </main>

                    <footer class="px-8 py-6 text-xs text-on-surface-variant/50 border-t border-outline-variant/10 bg-slate-50/50">
                        © {{ date('Y') }} {{ $appTitle }} • Admin Dashboard v2.0
                    </footer>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const toggleBtn = document.getElementById('mobile-menu-toggle');
                    const sidebar = document.getElementById('admin-sidebar');
                    const backdrop = document.getElementById('mobile-backdrop');
                    
                    if (!toggleBtn || !sidebar || !backdrop) return;
                    
                    const topBar = toggleBtn.querySelector('span:nth-child(1)');
                    const middleBar = toggleBtn.querySelector('span:nth-child(2)');
                    const bottomBar = toggleBtn.querySelector('span:nth-child(3)');

                    function toggleMenu() {
                        const isOpen = !sidebar.classList.contains('-translate-x-full');
                        
                        if (isOpen) {
                            // Close
                            sidebar.classList.add('-translate-x-full');
                            backdrop.classList.add('opacity-0');
                            setTimeout(() => backdrop.classList.add('hidden'), 300);
                            
                            // Animate hamburger back to 3 lines
                            topBar.style.transform = 'none';
                            middleBar.style.opacity = '1';
                            bottomBar.style.transform = 'none';
                        } else {
                            // Open
                            sidebar.classList.remove('-translate-x-full');
                            backdrop.classList.remove('hidden');
                            // Trigger reflow UI sync
                            void backdrop.offsetWidth;
                            backdrop.classList.remove('opacity-0');
                            
                            // Animate to X (distance between top/bottom and center)
                            topBar.style.transform = 'translateY(5px) rotate(45deg)';
                            middleBar.style.opacity = '0';
                            bottomBar.style.transform = 'translateY(-5px) rotate(-45deg)';
                        }
                    }

                    toggleBtn.addEventListener('click', toggleMenu);
                    backdrop.addEventListener('click', toggleMenu);
                });
            </script>
        </body>
    @else
        <body class="min-h-dvh bg-transparent text-neutral-900">
            <div class="min-h-dvh flex flex-col">
                <header class="border-b border-white/20 bg-white/70 backdrop-blur-md">
                    <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-x-4 gap-y-2 px-4 py-3 sm:py-4">
                        <a href="{{ url('/') }}" class="font-semibold tracking-tight text-lg">
                            {{ $appTitle }}
                        </a>

                        <nav class="flex items-center gap-1 sm:gap-2 text-sm">
                            @if (session('admin_authed'))
                                @include('admin.partials.nav')
                                <form method="post" action="{{ url('/admin/logout') }}">
                                    @csrf
                                    <button class="rounded-md px-2 py-1.5 hover:bg-black/5 whitespace-nowrap sm:px-3">Đăng xuất</button>
                                </form>
                            @else
                                <a
                                    href="{{ url('/login') }}"
                                    class="rounded-md px-3 py-1.5 hover:bg-black/5"
                                >Đăng ký</a>
                                <a
                                    href="{{ url('/admin/login') }}"
                                    class="rounded-md px-3 py-1.5 hover:bg-black/5"
                                >Admin</a>
                            @endif
                        </nav>
                    </div>
                </header>

                <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-10">
                    @if (session('status'))
                        <div class="mb-6 rounded-2xl border border-emerald-200/60 bg-emerald-50/90 px-4 py-3 text-emerald-900 backdrop-blur">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-rose-200/70 bg-rose-50/90 px-4 py-3 text-rose-900 backdrop-blur">
                            <div class="font-medium">Có lỗi xảy ra</div>
                            <ul class="mt-2 list-disc pl-5 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot ?? '' }}
                    @yield('content')
                </main>

                <footer class="border-t border-white/20 bg-white/60 backdrop-blur-md">
                    <div class="mx-auto max-w-6xl px-4 py-5 text-xs text-neutral-600">
                        © {{ date('Y') }} {{ $appTitle }}
                    </div>
                </footer>
            </div>
        </body>
    @endif
</html>

