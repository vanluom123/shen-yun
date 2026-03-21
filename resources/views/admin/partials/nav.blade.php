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
            if ($item['url'] === '/admin') {
                $active = request()->is('admin');
            } else {
                $active = request()->is(ltrim($item['url'], '/').'*');
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

