@extends('layouts.app', ['title' => 'Đăng ký thành công'])

@section('content')
    <div class="text-center">
        <div class="rsvp-heading text-champagne-gold">Đăng ký thành công</div>
        <div class="mt-2 text-sm text-neutral-200/75">
            Cảm ơn {{ $registration->full_name }} — mã đăng ký <span
                class="font-mono font-semibold text-[#f3e2b6]">#{{ $registration->id }}</span>
        </div>
    </div>

    <div class="guest-infor mt-2 rounded-xl border p-6">
        <div>
            <div>
                <span class="text-xs tracking-[0.2em] text-neutral-200/75">TỔNG SỐ KHÁCH: </span>
                <span class="mt-1 text-2xl font-semibold text-neutral-100">{{ $registration->total_count }}</span>
            </div>
            <div>
                <span class="text-xs tracking-[0.2em] text-neutral-200/75">THỜI GIAN:</span>
                <span class="mt-1 text-lg font-semibold text-[#d9b76f]">
                    {{ $registration->eventSession->starts_at->format('d/m/Y H:i') }}
                </span>
            </div>
            <div>
                <span class="text-xs tracking-[0.2em] text-neutral-200/75">ĐỊA ĐIỂM:</span>
                <div class="mt-1 text-lg font-semibold text-[#d9b76f]">{{ $registration->eventSession->venue->name }}</div>
                @if ($registration->eventSession->venue->address)
                    <div class="mt-1 text-sm text-neutral-200/80">{{ $registration->eventSession->venue->address }}</div>
                @endif
            </div>
            <div class="success-note">
                <p>Quý khách vui lòng đến trước 15 phút!</p>
                <p>Trang phục trang nhã, lịch sự!</p>
                <p>Hân hạnh đón tiếp Quý khách!</p>
            </div>
        </div>
    </div>

    <div class="mt-6 flex items-center justify-between gap-4 flex-wrap">
        <a href="{{ url('/') }}" class="btn btn-dark flex-1">TRANG CHỦ</a>
        <a href="{{ url('/register') }}" class="btn btn-gold flex-1">ĐĂNG KÝ MỚI</a>
    </div>
@endsection