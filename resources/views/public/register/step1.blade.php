@extends('layouts.app', ['title' => 'Bước 1: Chọn lịch hẹn'])

@section('content')
    @include('public.register.partials.stepper', ['currentStep' => 1])

    <div class="text-center">
        <div class="rsvp-heading text-champagne-gold">Bước 1: Chọn lịch hẹn</div>
        <div class="mt-2 text-sm text-neutral-200/75">Chọn địa điểm tổ chức và ngày diễn ra sự kiện.</div>
    </div>

    @php
        $dow = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
        $selectedVenueId = old('venue_id', request('venue_id', $draft['venue_id'] ?? ''));
        $selectedSessionId = old('event_session_id', $draft['event_session_id'] ?? '');
    @endphp

    <form method="post" action="{{ url('/register/step1') }}" class="mt-6 space-y-6" novalidate>
        @csrf

        {{-- Venue selector --}}
        <div>
            <x-custom-select name="venue_id" :options="$venues" :selected="$selectedVenueId" label="Địa điểm tổ chức"
                placeholder="Chọn địa điểm" :required="true" :error="$errors->first('venue_id')"
                onchange="window.location='{{ url('/register') }}?venue_id=' + value" />
        </div>

        {{-- Session picker --}}
        <div>
            <div class="rsvp-label">Ngày diễn ra sự kiện <span class="text-red-500">*</span></div>

            @if ($sessions->isEmpty())
                <div class="rounded-xl border border-neutral-500/30 bg-black/25 px-4 py-3 text-sm text-neutral-200/80">
                    Hiện chưa có trình chiếu nào sắp diễn ra. Vui lòng quay lại sau hoặc liên hệ ban tổ chức.
                </div>
            @else
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($sessions as $s)
                        @php
                            $remaining = max(0, $s->capacity_total - $s->capacity_reserved);
                            $dayIndex = (int) $s->starts_at->timezone('Asia/Ho_Chi_Minh')->format('w');
                            $dateLabel = $dow[$dayIndex] . ', ' . $s->starts_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y');
                            $timeLabel = $s->starts_at->timezone('Asia/Ho_Chi_Minh')->format('H:i');
                            $isSelected = (string) $selectedSessionId === (string) $s->id;
                            $isPostponed = $s->isPaused();
                            $isFullyBooked = $remaining <= 0;
                            $isDisabled = $isPostponed || $isFullyBooked;
                        @endphp
                        <label class="block {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                            <input type="radio" name="event_session_id" value="{{ $s->id }}" class="sr-only" data-session-radio
                                required {{ $isDisabled ? 'disabled' : '' }} {{ $isSelected ? 'checked' : '' }} />
                            <div class="rsvp-card text-center {{ $isSelected ? 'rsvp-card-selected' : '' }} {{ $isDisabled ? 'border-neutral-500/30 bg-black/10' : '' }}"
                                data-session-card>
                                <div class="rsvp-card-date text-base font-semibold text-[#d9b76f]">{{ $dateLabel }}</div>
                                <div class="rsvp-card-time mt-0.5 text-xs text-neutral-300">{{ $timeLabel }}</div>
                                <div class="rsvp-card-status italic mt-1 text-sm text-neutral-200">
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

        <div class="flex justify-center pt-2">
            <button class="btn btn-gold">TIẾP TỤC</button>
        </div>
    </form>
@endsection