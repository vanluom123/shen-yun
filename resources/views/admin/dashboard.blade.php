@extends('layouts.app', ['title' => 'Admin'])

@section('content')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Admin</h1>
            <p class="mt-1 text-sm text-neutral-600">Khu quản trị (đang triển khai CRUD theo todo tiếp theo).</p>
        </div>

        <form method="post" action="{{ url('/admin/logout') }}">
            @csrf
            <button class="rounded-xl border border-neutral-300 bg-white px-4 py-2 text-sm font-medium hover:bg-neutral-50">
                Đăng xuất
            </button>
        </form>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <a href="{{ url('/admin/venues') }}" class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm hover:border-neutral-400">
            <div class="text-sm font-semibold">Quản lý địa điểm</div>
            <div class="mt-1 text-sm text-neutral-600">Thêm, sửa, xóa địa điểm tổ chức.</div>
        </a>
        <div class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-semibold">Quản lý trình chiếu</div>
            <div class="mt-1 text-sm text-neutral-600">Sắp có: CRUD sessions + danh sách đăng ký.</div>
        </div>
    </div>
@endsection

