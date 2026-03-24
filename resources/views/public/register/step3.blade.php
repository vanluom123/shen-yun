@extends('layouts.app', ['title' => 'Bước 3: Số lượng'])

@section('content')
    @include('public.register.partials.stepper', ['currentStep' => 3])

    <div class="text-center">
        <div class="rsvp-heading text-champagne-gold">Bước 3: Số lượng khách</div>
        <div class="mt-2 text-sm text-neutral-200/75">Chọn số lượng khách tham dự.</div>
    </div>

    @php
        $attendWithGuest = (bool) ($draft['attend_with_guest'] ?? false);

        $defaults = [
            // NOTE: counters represent number of guests (do NOT include registrant).
            // If "attend_with_guest" is on, the system will auto-add 1 seat for the registrant.
            'adult_count' => 0,
            'ntl_count' => 0,
            'ntl_new_count' => 0,
            'child_count' => 0,
        ];

        $values = [];
        foreach ($defaults as $k => $v) {
            $values[$k] = (int) old($k, $draft[$k] ?? $v);
        }

        $rows = [
            ['adult_count', 'Khách'],
            ['ntl_count', 'NTL'],
            ['ntl_new_count', 'NTL mới'],
            ['child_count', 'Trẻ em'],
        ];
    @endphp

    @if ($attendWithGuest)
        <div class="rsvp-banner mt-5">
            ✨ Vui lòng tính cả số lượng của bạn vì bạn có đi cùng khách
        </div>
    @else
        <div class="mb-3 mt-5 p-3 rounded-md border border-red-700/40 bg-red-900/30 text-red-200 text-sm">
            ❗Vui lòng <strong>không</strong> tính số lượng của bạn vì bạn <strong>không</strong> đi cùng khách
        </div>
    @endif
    <form method="post" action="{{ url('/register/step3') }}" class="mt-6 space-y-6" data-counter-root data-counter-self="0"
        novalidate>
        @csrf

        <div class="space-y-5">
            @foreach ($rows as [$name, $label])
                <div class="flex items-center justify-between gap-4" data-counter-row>
                    <div class="text-lg font-semibold text-neutral-100/85">{{ $label }}</div>

                    <div class="flex items-center gap-3">
                        <button type="button" class="rsvp-counter-btn" data-counter-dec
                            aria-label="Giảm {{ $label }}">−</button>

                        <input type="number" name="{{ $name }}" value="{{ $values[$name] }}" min="0" step="1" data-counter-input
                            data-counter-display class="rsvp-counter-input" />

                        <button type="button" class="rsvp-counter-btn" data-counter-inc
                            aria-label="Tăng {{ $label }}">+</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-t border-[#d9b76f]/25 pt-6">
            <div class="flex items-end justify-between gap-4">
                <div class="text-xl font-semibold tracking-tight text-[#d9b76f]">Tổng cộng:</div>
                <div class="text-2xl font-semibold tracking-tight text-neutral-100">
                    <span data-counter-total>0</span> khách
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-4 pt-2 flex-wrap">
            <a href="{{ url('/register/step2') }}" class="btn btn-dark flex-1">QUAY LẠI</a>
            <button type="submit" class="btn btn-gold flex-1">TIẾP TỤC</button>
        </div>
    </form>
@endsection