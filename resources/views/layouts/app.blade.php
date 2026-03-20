<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Phong trà') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $isRegisterFlow = request()->is('register*') || request()->is('login');
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

