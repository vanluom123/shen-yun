@extends('layouts.app', ['title' => 'Tổng quan'])

@section('content')
    <div class="space-y-10">
        <!-- Dashboard Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-on-surface mb-2">Chào mừng, Admin</h1>
                <p class="text-on-surface-variant max-w-md">Chào mừng bạn quay lại hệ thống quản lý. Tại đây bạn có thể quản lý các điểm sự kiện và lịch trình chiếu.</p>
            </div>
            
            <form method="post" action="{{ url('/admin/logout') }}">
                @csrf
                <button class="px-6 py-2.5 bg-surface-container-high hover:bg-surface-container-highest text-on-surface border border-outline-variant/30 rounded-full text-sm font-bold active:scale-95 transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">logout</span>
                    Đăng xuất
                </button>
            </form>
        </div>

        <!-- Quick Stats / Shortcuts -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ url('/admin/venues') }}" class="admin-card p-6 group hover:translate-y-[-4px] transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary mb-4 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl fill-1">location_on</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant/30 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </div>
                <h3 class="text-lg font-bold text-on-surface mb-1">Quản lý địa điểm</h3>
                <p class="text-sm text-on-surface-variant leading-relaxed">Quản lý danh sách các địa điểm tổ chức sự kiện, thêm mới hoặc cập nhật thông tin.</p>
            </a>

            <a href="{{ url('/admin/sessions') }}" class="admin-card p-6 group hover:translate-y-[-4px] transition-all duration-300">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-xl bg-secondary-container flex items-center justify-center text-on-secondary-container mb-4 group-hover:scale-110 transition-transform">
                        <span class="material-symbols-outlined text-2xl fill-1">present_to_all</span>
                    </div>
                    <span class="material-symbols-outlined text-on-surface-variant/30 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </div>
                <h3 class="text-lg font-bold text-on-surface mb-1">Quản lý trình chiếu</h3>
                <p class="text-sm text-on-surface-variant leading-relaxed">Theo dõi các lịch chiếu, trạng thái đăng ký và quản lý số lượng khách mời.</p>
            </a>

            <div class="admin-card p-6 opacity-60">
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-xl bg-surface-container-high flex items-center justify-center text-on-surface-variant mb-4">
                        <span class="material-symbols-outlined text-2xl">settings</span>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-on-surface mb-1">Cài đặt hệ thống</h3>
                <p class="text-sm text-on-surface-variant leading-relaxed">Tính năng đang được phát triển. Bạn sẽ sớm có thể cấu hình các tham số hệ thống.</p>
            </div>
        </div>
    </div>
@endsection

