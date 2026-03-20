@php
    $items = [
        ['url' => '/admin', 'label' => 'Danh sách', 'icon' => 'list_alt'],
        ['url' => '/admin/venues', 'label' => 'Địa điểm', 'icon' => 'location_on'],
        ['url' => '/admin/sessions', 'label' => 'Trình chiếu', 'icon' => 'present_to_all'],
    ];
@endphp

<div class="space-y-1">
    @foreach ($items as $item)
        @php
            $active = request()->is(ltrim($item['url'], '/').'*');
            // Specific check for home to avoid matching all admin subpaths if url is exactly /admin
            if ($item['url'] === '/admin' && request()->path() !== 'admin') {
                $active = false;
            }
        @endphp
        <a 
            href="{{ url($item['url']) }}" 
            class="flex items-center gap-3 px-3 py-2 text-sm font-medium transition-all duration-200 rounded-xl group {{ $active ? 'bg-white shadow-sm text-primary' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}"
        >
            <span class="material-symbols-outlined text-xl {{ $active ? 'fill-1' : 'text-on-surface-variant/70 group-hover:text-on-surface' }}">
                {{ $item['icon'] }}
            </span>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</div>

