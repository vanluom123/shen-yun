@extends('layouts.app', ['title' => 'Bước 1: Chọn lịch hẹn'])

@section('content')
    @include('public.register.partials.stepper', ['currentStep' => 1])

    <div class="text-center">
        <div class="rsvp-heading">Bước 1: Chọn lịch hẹn</div>
        <div class="mt-2 text-sm text-neutral-200/75">Chọn địa điểm tổ chức và ngày diễn ra sự kiện.</div>
    </div>

    @php
        $dow = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
        $selectedVenueId  = old('venue_id', request('venue_id', $draft['venue_id'] ?? ''));
        $selectedSessionId = old('event_session_id', $draft['event_session_id'] ?? '');
    @endphp

    <form method="post" action="{{ url('/register/step1') }}" class="mt-6 space-y-6">
        @csrf

        {{-- Venue selector --}}
        <div>
            <div class="rsvp-label">Địa điểm tổ chức</div>
            <select
                id="venue_id"
                name="venue_id"
                class="rsvp-select"
                required
                onchange="window.location='{{ url('/register') }}?venue_id=' + this.value"
            >
                <option value="" disabled {{ empty($selectedVenueId) ? 'selected' : '' }}>Chọn địa điểm…</option>
                @foreach ($venues as $v)
                    <option value="{{ $v->id }}" {{ (string) $selectedVenueId === (string) $v->id ? 'selected' : '' }}>
                        {{ $v->name }}{{ $v->address ? ' — '.$v->address : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Session picker --}}
        <div>
            <div class="rsvp-label">Ngày diễn ra sự kiện</div>

            @if ($sessions->isEmpty())
                <div class="mt-3 rounded-2xl border border-neutral-500/30 bg-black/25 px-4 py-3 text-sm text-neutral-200/80">
                    Hiện chưa có trình chiếu nào sắp diễn ra. Vui lòng quay lại sau hoặc liên hệ ban tổ chức.
                </div>
            @else
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($sessions as $s)
                        @php
                            $remaining     = max(0, $s->capacity_total - $s->capacity_reserved);
                            $dayIndex      = (int) $s->starts_at->timezone('Asia/Ho_Chi_Minh')->format('w');
                            $dateLabel     = $dow[$dayIndex].', '.$s->starts_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y');
                            $timeLabel     = $s->starts_at->timezone('Asia/Ho_Chi_Minh')->format('H:i');
                            $isSelected    = (string) $selectedSessionId === (string) $s->id;
                            $isPostponed   = $s->isPaused();
                            $isFullyBooked = $remaining <= 0;
                            $isDisabled    = $isPostponed || $isFullyBooked;
                        @endphp
                        <label class="block {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                            <input
                                type="radio"
                                name="event_session_id"
                                value="{{ $s->id }}"
                                class="sr-only"
                                data-session-radio
                                required
                                {{ $isDisabled ? 'disabled' : '' }}
                                {{ $isSelected ? 'checked' : '' }}
                            />
                            <div
                                class="rsvp-card {{ $isSelected ? 'rsvp-card-selected' : '' }} {{ $isDisabled ? 'border-neutral-500/30 bg-black/10' : '' }}"
                                data-session-card
                            >
                                <div class="text-base font-semibold text-[#f3e2b6]">{{ $dateLabel }}</div>
                                <div class="mt-0.5 text-xs text-neutral-400">{{ $timeLabel }}</div>
                                <div class="mt-1 text-sm text-neutral-200/75">
                                    @if ($isPostponed)
                                        Tạm hoãn
                                    @elseif ($isFullyBooked)
                                        Hết chỗ
                                    @else
                                        còn {{ $remaining }} chỗ
                                    @endif
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif

            @error('event_session_id')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between gap-4 pt-2">
            <button
                type="submit"
                formaction="{{ url('/register/logout') }}"
                formmethod="post"
                formnovalidate
                class="btn-dark px-6 py-3 text-xs"
            >
                THOÁT
            </button>

            <button class="btn-gold">TIẾP TỤC</button>
        </div>
    </form>
@endsection
