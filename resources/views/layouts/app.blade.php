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
                    <div class="rsvp-subtitle">Trải nghiệm hành trình âm nhạc &amp; vũ đạo thuần chính</div>
                </div>

                <main class="pb-12">
                    <div class="rsvp-panel">

                        @yield('content')

                    </div>
                </main>
            </div>
            <x-floating-contact-widget />
        </body>
    @elseif ($isAdmin)
        <body class="min-h-dvh bg-background text-on-surface antialiased font-sans">
            <div id="admin-layout" class="flex min-h-dvh flex-col lg:flex-row relative">
                <!-- Mobile Backdrop -->
                <div id="mobile-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity duration-300 opacity-0 lg:hidden"></div>

                <!-- Sidebar -->
                <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-50 transform -translate-x-full transition-all duration-300 ease-in-out lg:fixed lg:translate-x-0 w-[280px] lg:w-64 bg-slate-50 border-r border-outline-variant/20 flex flex-col p-4 shadow-xl lg:shadow-none h-dvh overflow-hidden">
                    <div class="sidebar-header px-2 mb-4 whitespace-nowrap overflow-hidden relative">
                        <div class="flex items-center justify-between">
                            <a href="{{ url('/admin') }}" class="flex flex-col items-center sidebar-brand group">
                                <img src="{{ asset('shen-yun.webp') }}" alt="Logo" class="logo-img h-16 w-16 rounded-full object-cover border-2 border-outline-variant/30 group-hover:border-primary/50 transition-colors">
                                <p class="text-xs text-on-surface-variant opacity-70 font-medium uppercase tracking-widest mt-2 group-hover:text-primary transition-colors">Admin Space</p>
                            </a>
                            <!-- Desktop Collapse Toggle -->
                            <button id="desktop-sidebar-toggle" class="hidden lg:block lg:flex p-2 text-on-surface-variant hover:text-on-surface focus:outline-none items-center justify-center w-10 h-10 rounded-xl hover:bg-surface-container-high transition-colors">
                                <span class="material-symbols-outlined transition-transform duration-300">menu_open</span>
                            </button>
                        </div>
                    </div>
                    
                    <nav class="flex-1 space-y-1">
                        @include('admin.partials.nav')
                    </nav>

                    <div class="mt-auto pt-4 border-t border-outline-variant/30">
                         <form method="post" action="{{ url('/admin/logout') }}">
                            @csrf
                            <button class="flex w-full items-center gap-3 px-3 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface rounded-xl transition-all group overflow-hidden whitespace-nowrap">
                                <span class="material-symbols-outlined text-xl group-hover:scale-110 transition-transform">logout</span>
                                <span class="sidebar-text">Đăng xuất</span>
                            </button>
                        </form>
                    </div>
                </aside>

                <!-- Main Area -->
                <div id="main-content" class="flex-1 flex flex-col min-w-0 lg:ml-64 transition-all duration-300">
                    <header class="h-16 bg-white/80 backdrop-blur-md border-b border-outline-variant/20 flex items-center px-4 sm:px-8 sticky top-0 z-30">
                        <div class="flex items-center w-full relative">
                            <button id="mobile-menu-toggle" class="lg:hidden p-2 -ml-2 text-on-surface-variant hover:text-on-surface focus:outline-none flex flex-col justify-center items-center w-10 h-10 relative z-10">
                                <span class="block w-5 h-0.5 bg-current transition-all duration-300 ease-in-out absolute top-[14px]"></span>
                                <span class="block w-5 h-0.5 bg-current transition-all duration-300 ease-in-out absolute top-[19px]"></span>
                                <span class="block w-5 h-0.5 bg-current transition-all duration-300 ease-in-out absolute bottom-[14px]"></span>
                            </button>
                            
                            <div class="lg:hidden absolute inset-0 flex items-center justify-center pointer-events-none">
                                <a href="{{ url('/admin') }}" class="pointer-events-auto">
                                    <img src="{{ asset('shen-yun.webp') }}" alt="Logo" class="h-13 w-13 rounded-full object-cover border border-outline-variant/30">
                                </a>
                            </div>

                            <div class="hidden lg:block">
                                <a href="{{ url('/admin') }}" class="text-sm font-semibold text-on-surface-variant/60 hover:text-primary underline transition-colors">Quản lý</a>
                                <span class="mx-1 text-on-surface-variant/30">/</span>
                                <span class="text-sm font-bold text-on-surface">{{ $title ?? 'Dashboard' }}</span>
                            </div>
                        </div>
                    </header>

                    <style>
                        @media (min-width: 1024px) {
                            .sidebar-collapsed #admin-sidebar {
                                width: 80px !important;
                            }
                            .sidebar-collapsed #admin-sidebar .sidebar-header {
                                padding-left: 0 !important;
                                padding-right: 0 !important;
                                text-align: center !important;
                                justify-content: center !important;
                            }
                            .sidebar-collapsed #admin-sidebar .sidebar-brand,
                            .sidebar-collapsed #admin-sidebar .sidebar-text {
                                display: none !important;
                            }
                            .sidebar-collapsed #admin-sidebar .sidebar-header .flex {
                                justify-content: center !important;
                            }
                            .sidebar-collapsed #main-content {
                                margin-left: 80px !important;
                            }
                            .sidebar-collapsed #desktop-sidebar-toggle span {
                                transform: rotate(180deg);
                            }
                            .sidebar-collapsed #admin-sidebar nav a,
                            .sidebar-collapsed #admin-sidebar .mt-auto button {
                                justify-content: center !important;
                                padding-left: 0 !important;
                                padding-right: 0 !important;
                                gap: 0 !important;
                            }
                            .sidebar-collapsed #admin-sidebar nav a span.material-symbols-outlined {
                                margin: 0 !important;
                            }
                        }
                        
                        /* Ensure transitions are smooth */
                        #admin-sidebar, #main-content {
                            transition: all 0.3s ease-in-out !important;
                        }
                    </style>

                    <main class="flex-1 p-5">
                        @if (session('status'))
                            <div class="mb-6 rounded-xl border border-emerald-200/60 bg-emerald-50/90 px-4 py-3 text-emerald-900 shadow-sm">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-6 rounded-xl border border-rose-200/70 bg-rose-50/90 px-4 py-3 text-rose-900 shadow-sm">
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
                    const desktopToggleBtn = document.getElementById('desktop-sidebar-toggle');
                    const sidebar = document.getElementById('admin-sidebar');
                    const backdrop = document.getElementById('mobile-backdrop');
                    const adminLayout = document.getElementById('admin-layout');
                    
                    if (!sidebar || !backdrop || !adminLayout) return;

                    // Initialize Desktop Sidebar State
                    const isCollapsed = localStorage.getItem('admin_sidebar_collapsed') === 'true';
                    if (isCollapsed && window.innerWidth >= 1024) {
                        adminLayout.classList.add('sidebar-collapsed');
                    }

                    // Desktop Toggle
                    if (desktopToggleBtn) {
                        desktopToggleBtn.addEventListener('click', () => {
                            adminLayout.classList.toggle('sidebar-collapsed');
                            localStorage.setItem('admin_sidebar_collapsed', adminLayout.classList.contains('sidebar-collapsed'));
                        });
                    }
                    
                    if (toggleBtn) {
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
                                
                                if (topBar) {
                                    topBar.style.transform = 'none';
                                    middleBar.style.opacity = '1';
                                    bottomBar.style.transform = 'none';
                                }
                            } else {
                                // Open
                                sidebar.classList.remove('-translate-x-full');
                                backdrop.classList.remove('hidden');
                                void backdrop.offsetWidth;
                                backdrop.classList.remove('opacity-0');
                                
                                if (topBar) {
                                    topBar.style.transform = 'translateY(5px) rotate(45deg)';
                                    middleBar.style.opacity = '0';
                                    bottomBar.style.transform = 'translateY(-5px) rotate(-45deg)';
                                }
                            }
                        }

                        toggleBtn.addEventListener('click', toggleMenu);
                        backdrop.addEventListener('click', toggleMenu);
                    }
                });
            </script>
        </body>
    @else
        <body class="min-h-dvh bg-transparent text-neutral-900">
            <div class="min-h-dvh flex flex-col admin-container">
                <header class="admin-header">
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
                        <div class="mb-6 rounded-xl border border-emerald-200/60 bg-emerald-50/90 px-4 py-3 text-emerald-900 backdrop-blur">
                            {{ session('status') }}
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
        @endif

        <div id="toast-container" class="toast-container"></div>

        <script>
            function showToast(messages) {
                const container = document.getElementById('toast-container');
                if (!container) return;

                if (!Array.isArray(messages)) messages = [messages];
                
                messages.forEach(msg => {
                    const toast = document.createElement('div');
                    toast.className = 'toast-notification';
                    toast.innerHTML = `
                        <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5 opacity-90">error</span>
                        <div class="flex-1 leading-relaxed">${msg}</div>
                    `;
                    
                    container.appendChild(toast);

                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        toast.classList.add('hide');
                        setTimeout(() => toast.remove(), 400);
                    }, 5000);
                    
                    // Allow manual click to dismiss
                    toast.onclick = () => {
                        toast.classList.add('hide');
                        setTimeout(() => toast.remove(), 400);
                    };
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Show server-side errors
                const serverErrors = @json($errors->all());
                if (serverErrors && serverErrors.length > 0) {
                    showToast(serverErrors);
                    
                    // Also try to find fields with errors and highlight them                    
                    const errorFields = @json($errors->keys());
                    errorFields.forEach(field => {
                        const input = document.getElementsByName(field)[0] || document.getElementById(field);
                        // Skip inputs on step 3 (they handle errors differently)
                        if (input && !input.classList.contains('rsvp-counter-input')) {
                            input.classList.add('is-invalid');
                        }
                    });
                }

                // Intercept form submissions for client-side feedback
                document.querySelectorAll('form').forEach(form => {
                    if (form.hasAttribute('formnovalidate')) return;
                    
                    form.addEventListener('submit', function(e) {
                        if (!form.checkValidity()) {
                            e.preventDefault();
                            showToast('Vui lòng kiểm tra lại các thông tin bắt buộc.');
                            
                            // Highlight invalid fields
                            form.querySelectorAll(':invalid').forEach(field => {
                                field.classList.add('is-invalid');
                                field.addEventListener('input', function() {
                                    if (this.checkValidity()) {
                                        this.classList.remove('is-invalid');
                                    }
                                }, { once: true });
                            });
                        }
                    });
                });
            });
        </script>
    </body>
</html>

