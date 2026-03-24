@extends('layouts.app', ['title' => 'Mở khoá đăng ký'])

@section('content')
    <div class="text-center">
        <div class="rsvp-heading">Mở khoá đăng ký</div>
    </div>

    <form method="post" action="{{ url('/login') }}" class="mx-auto mt-6 w-full max-w-sm space-y-4" novalidate>
        @csrf

        <div>
            <div class="rsvp-label">Mật khẩu</div>
            <div class="relative mt-2">
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="rsvp-input mt-0 pr-16 @error('password') is-invalid @enderror"
                />
                <button
                    type="button"
                    class="absolute inset-y-0 right-2 my-auto h-9 rounded-xl border border-neutral-500/20 bg-black/10 px-3 text-xs font-semibold text-neutral-100/80 hover:bg-black/16"
                    data-toggle-password="password"
                    aria-pressed="false"
                    aria-label="Hiện mật khẩu"
                >HIỆN</button>
            </div>
        </div>

        <button type="submit" class="btn btn-gold w-full">
            TIẾP TỤC
        </button>

        <div class="mt-8 text-center">
            <a href="{{ url('/admin/login') }}" class="text-xs text-neutral-400 hover:text-[#f3e2b6] transition-colors">
                Bạn là quản trị viên? Đăng nhập tại đây
            </a>
        </div>
    </form>
@endsection

