@extends('layouts.app', ['title' => 'Admin login'])

@section('content')
    <div class="mx-auto max-w-md login-container">
        <div class="border border-neutral-200 bg-white p-6 shadow-sm rounded-xl">
            <h1 class="text-xl font-semibold tracking-tight">Đăng nhập Admin</h1>
            <p class="mt-1 text-sm text-neutral-600">
                Nhập <span class="font-medium">AdminPassword</span> để vào khu quản trị.
            </p>

            <form method="post" action="{{ url('/admin/login') }}" class="mt-5 space-y-4" novalidate>
                @csrf

                <div>
                    <label class="text-sm font-medium" for="password">Mật khẩu</label>
                    <div class="relative mt-2">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="w-full rsvp-field rounded-xl border border-neutral-300 bg-white px-3 py-2 pr-16 text-sm outline-none ring-0 focus:border-neutral-900 @error('password') is-invalid @enderror" />
                        <button type="button"
                            class="absolute inset-y-0 right-2 my-auto h-9 rounded-lg px-3 text-xs font-semibold text-neutral-700 hover:bg-neutral-100"
                            data-toggle-password="password" aria-pressed="false" aria-label="Hiện mật khẩu">HIỆN</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-gold">
                    Vào admin
                </button>
            </form>
        </div>
    </div>
@endsection