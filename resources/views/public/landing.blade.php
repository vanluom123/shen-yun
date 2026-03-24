@extends('layouts.app', ['title' => config('app.name', 'Phong trà')])

@section('content')
    <div class="grid gap-8 lg:grid-cols-12 lg:items-start">
        <div class="lg:col-span-7">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="inline-flex items-center gap-2 rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-700">
                    RSVP • Đăng ký tham dự
                </div>

                <h1 class="mt-4 text-balance text-3xl font-semibold tracking-tight sm:text-4xl">
                    Đăng ký tham dự trình chiếu phòng trà
                </h1>

                <p class="mt-3 text-pretty text-neutral-600">
                    Vui lòng đi theo từng bước để chọn địa điểm/trình chiếu, nhập thông tin và số lượng khách.
                    Hệ thống sẽ tự động chặn đăng ký vượt quá số chỗ của trình chiếu.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a
                        href="{{ url('/login') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-neutral-900 px-5 py-3 text-sm font-medium text-white hover:bg-neutral-800"
                    >
                        Bắt đầu đăng ký
                    </a>
                    <a
                        href="{{ url('/admin/login') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-neutral-300 bg-white px-5 py-3 text-sm font-medium text-neutral-900 hover:bg-neutral-50"
                    >
                        Vào trang Admin
                    </a>
                </div>

                <div class="mt-6 rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700">
                    <div class="font-medium">Lưu ý</div>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>Khách cần nhập <span class="font-medium">GuestPassword</span> để vào form đăng ký.</li>
                        <li>Admin cần nhập <span class="font-medium">AdminPassword</span> để quản trị.</li>
                        <li>Dữ liệu được lưu theo session của trình duyệt (đóng browser sẽ mất).</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
                <div class="text-sm font-semibold">Bạn sẽ làm được gì?</div>
                <ul class="mt-3 space-y-3 text-sm text-neutral-700">
                    <li class="flex gap-3">
                        <div class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></div>
                        <div>Đăng ký theo wizard 4 bước, validate rõ ràng.</div>
                    </li>
                    <li class="flex gap-3">
                        <div class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></div>
                        <div>Giới hạn chỗ theo từng trình chiếu (an toàn khi submit đồng thời).</div>
                    </li>
                    <li class="flex gap-3">
                        <div class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></div>
                        <div>Admin CRUD địa điểm/trình chiếu, export CSV danh sách khách.</div>
                    </li>
                    <li class="flex gap-3">
                        <div class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></div>
                        <div>Gửi email xác nhận qua SMTP (Gmail App Password).</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

