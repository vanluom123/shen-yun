@php
    $steps = [
        1 => 'Chọn trình chiếu',
        2 => 'Thông tin',
        3 => 'Số lượng',
        4 => 'Xác nhận',
    ];
@endphp

<div>
    <div class="relative mx-auto max-w-xl mb-8">
        <div class="absolute left-4 right-4 top-1/2 h-px -translate-y-1/2 bg-[#d9b76f]/25"></div>
        <div class="relative flex items-center justify-between">
            @foreach ($steps as $num => $label)
                @php
                    $active = ($currentStep ?? 1) === $num;
                    $done = ($currentStep ?? 1) > $num;
                @endphp
                <div class="flex flex-col items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl border text-sm font-semibold {{ $active ? 'border-[#d9b76f]/70 bg-[#d9b76f] text-black' : ($done ? 'border-[#d9b76f]/60 bg-[#3d453d] text-[#f3e2b6]' : 'border-[#d9b76f]/25 bg-[#3d453d] text-neutral-200') }}"
                        title="{{ $label }}">
                        @if ($done)
                            ✓
                        @else
                            {{ $num }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>