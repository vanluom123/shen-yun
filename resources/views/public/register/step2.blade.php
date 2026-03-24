@extends('layouts.app', ['title' => 'Bước 2: Thông tin'])

@section('content')
    @include('public.register.partials.stepper', ['currentStep' => 2])

    <div class="text-center">
        <div class="rsvp-heading text-champagne-gold">Bước 2: Thông tin người mời khách</div>
        <!-- <div class="mt-2 text-sm text-neutral-200/75">Nhập thông tin để nhận email xác nhận.</div> -->
    </div>

    @php
        $phone = (string) old('phone', $draft['phone'] ?? '');
        $parsedCountry = null;
        $parsedNumber = null;
        if (preg_match('/^(\\+(?:84|1|82|81|65))(\\d+)$/', $phone, $m)) {
            $parsedCountry = $m[1];
            $parsedNumber = $m[2];
        }
        $phoneCountry = (string) old('phone_country', $parsedCountry ?? '+84');
        $phoneNumber = (string) old('phone_number', $parsedNumber ?? preg_replace('/\\D+/', '', $phone));
    @endphp

    <form method="post" action="{{ url('/register/step2') }}" class="mt-6 space-y-5" novalidate>
        @csrf

        <div>
            <div class="rsvp-label">Họ &amp; Tên <span class="text-red-500">*</span>:</div>
            <input id="full_name" name="full_name" value="{{ old('full_name', $draft['full_name'] ?? '') }}" required
                class="rsvp-input @error('full_name') is-invalid @enderror" />
        </div>

        <div>
            <div class="rsvp-label">Số liên hệ (Signal/Phone) <span class="text-red-500">*</span>:</div>
            <div class="mt-2 grid grid-cols-3 gap-3">
                <x-custom-select name="phone_country" :options="[
            '+84' => '+84 VN',
            '+1' => '+1 US',
            '+82' => '+82 KR',
            '+81' => '+81 JP',
            '+65' => '+65 SG',
        ]" :selected="$phoneCountry"
                    :error="$errors->first('phone_country')" />
                <input type="tel" name="phone_number" inputmode="numeric" value="{{ $phoneNumber }}" placeholder="Nhập số…"
                    required class="rsvp-input col-span-2 @error('phone_number') is-invalid @enderror" />
            </div>
            <div class="mt-2 text-xs text-neutral-200/60">Số Signal sẽ dùng để gửi cập nhật.</div>
        </div>
        <div>
            <div class="rsvp-label">Email:</div>
            <input id="email" name="email" type="email" value="{{ old('email', $draft['email'] ?? '') }}"
                class="rsvp-input @error('email') is-invalid @enderror" />
        </div>
        <label class="mt-2 flex items-center gap-4  py-4">
            <span
                class="toggle-switch relative inline-flex h-8 w-13 items-center rounded-3xl border border-neutral-500/40 px-1 peer-checked:bg-[#d9b76f]">
                <input type="checkbox" name="attend_with_guest" value="1" class="peer sr-only" {{ old('attend_with_guest', $draft['attend_with_guest'] ?? false) ? 'checked' : '' }} />
                <span class="h-6 w-6 z-10 rounded-full bg-neutral-200 transition peer-checked:translate-x-5"></span>
                <span
                    class="absolute top-0 left-0 h-8 w-13 items-center rounded-3xl border border-neutral-500/40 bg-white/30 px-1 peer-checked:bg-[#d9b76f]"></span>
            </span>
            <div class="font-semibold tracking-wide text-white">Tôi sẽ tham dự cùng khách!</div>
        </label>
        <div class="flex items-center justify-between gap-4 pt-2 flex-wrap">
            <a href="{{ url('/register') }}" class="btn btn-dark flex-1">QUAY LẠI</a>
            <button type="submit" class="btn btn-gold flex-1">TIẾP TỤC</button>
        </div>
    </form>
@endsection