@php
    $items = [
        ['url' => '/admin/registrations', 'label' => 'Danh sách', 'icon' => 'list_alt'],
        ['url' => '/admin/venues', 'label' => 'Địa điểm', 'icon' => 'location_on'],
        ['url' => '/admin/sessions', 'label' => 'Trình chiếu', 'icon' => 'present_to_all'],
    ];
@endphp

<div class="space-y-1">
    @foreach ($items as $item)
        @php
            $active = request()->is(ltrim($item['url'], '/').'*');
        @endphp
        <a 
            href="{{ url($item['url']) }}" 
            class="flex items-center gap-3 px-3 py-2 text-sm font-medium transition-all duration-200 rounded-xl group {{ $active ? 'bg-white shadow-sm text-primary' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }} overflow-hidden whitespace-nowrap"
        >
            <span class="material-symbols-outlined text-xl {{ $active ? 'fill-1' : 'text-on-surface-variant/70 group-hover:text-on-surface' }}">
                {{ $item['icon'] }}
            </span>
            <span class="sidebar-text">{{ $item['label'] }}</span>
        </a>
    @endforeach

    <div class="mt-8 border-t border-outline-variant/30">
        <form method="post" action="{{ url('/admin/logout') }}">
            @csrf
            <button class="cursor-pointer flex w-full items-center gap-3 px-3 py-2 text-sm font-medium text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface rounded-xl transition-all group overflow-hidden whitespace-nowrap">
                <span class="material-symbols-outlined text-xl text-on-surface-variant/70 group-hover:text-on-surface group-hover:scale-110 transition-transform">logout</span>
                <span class="sidebar-text">Đăng xuất</span>
            </button>
        </form>
    </div>
</div>

