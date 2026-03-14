@extends('layouts.app', ['title' => 'Bước 4: Xác nhận'])

@section('content')
    @include('public.register.partials.stepper', ['currentStep' => 4])

    <div class="text-center">
        <div class="rsvp-heading">Thêm ghi chú</div>
        <div class="mt-2 text-sm text-neutral-200/75">Kiểm tra lại thông tin trước khi gửi đăng ký.</div>
    </div>

    <div class="mt-6 rounded-3xl border border-neutral-500/25 bg-black/30 p-6 shadow-[inset_0_0_0_1px_rgba(217,183,111,0.12)]">
        <div class="space-y-6">
            <div>
                <div class="text-xs tracking-[0.2em] text-neutral-200/60">QUÝ KHÁCH</div>
                <div class="mt-1 text-2xl font-semibold text-neutral-100">{{ $draft['full_name'] ?? '—' }}</div>
                <div class="mt-1 text-sm text-neutral-200/70">{{ $draft['phone'] ?? '' }}</div>
            </div>

            <div class="border-t border-[#d9b76f]/20 pt-5">
                <div class="text-xs tracking-[0.2em] text-neutral-200/60">ĐỊA ĐIỂM</div>
                <div class="mt-1 text-lg font-semibold text-[#d9b76f]">{{ $venue?->name ?? '—' }}</div>
            </div>

            <div class="border-t border-[#d9b76f]/20 pt-5">
                <div class="text-xs tracking-[0.2em] text-neutral-200/60">THỜI GIAN</div>
                <div class="mt-1 text-lg font-semibold text-[#d9b76f]">
                    {{ $session?->starts_at?->format('d/m/Y H:i') ?? '—' }}
                </div>
            </div>

            <div class="border-t border-[#d9b76f]/20 pt-5">
                <div class="text-xs tracking-[0.2em] text-neutral-200/60">TRẠNG THÁI THAM DỰ</div>
                <div class="mt-1 text-base font-semibold {{ !empty($draft['attend_with_guest']) ? 'text-emerald-300' : 'text-neutral-200/80' }}">
                    {{ !empty($draft['attend_with_guest']) ? 'Sẽ tham dự cùng khách' : 'Không đi cùng khách' }}
                </div>
            </div>

            <div class="border-t border-[#d9b76f]/20 pt-5">
                <div class="text-xs tracking-[0.2em] text-neutral-200/60">SỐ LƯỢNG KHÁCH</div>
                <div class="mt-3 grid gap-3 sm:grid-cols-2 text-sm text-neutral-200/85">
                    <div>Khách: <span class="font-semibold text-neutral-100">{{ $draft['adult_count'] ?? 0 }}</span></div>
                    <div>NTL: <span class="font-semibold text-neutral-100">{{ $draft['ntl_count'] ?? 0 }}</span></div>
                    <div>NTL mới: <span class="font-semibold text-neutral-100">{{ $draft['ntl_new_count'] ?? 0 }}</span></div>
                    <div>Trẻ em: <span class="font-semibold text-neutral-100">{{ $draft['child_count'] ?? 0 }}</span></div>
                </div>
                <div class="mt-4 text-lg font-semibold text-[#d9b76f]">
                    Tổng cộng: <span class="text-neutral-100">{{ $draft['total_count'] ?? 0 }}</span> khách
                    @if (!empty($draft['attend_with_guest']))
                        <span class="text-sm font-medium text-neutral-200/70">(đã gồm bạn)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="{{ url('/register/submit') }}" class="mt-6 flex items-center justify-between gap-4">
        @csrf
        <a href="{{ url('/register/step3') }}" class="btn-dark px-6 py-3 text-xs">QUAY LẠI</a>
        <button class="btn-gold">GỬI ĐĂNG KÝ</button>
    </form>
@endsection

