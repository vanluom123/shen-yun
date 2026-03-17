@extends('layouts.app', ['title' => 'Admin – Chỉnh sửa đăng ký'])

@section('content')
    <div class="rounded-3xl border border-white/25 bg-white/65 p-5 shadow-[0_16px_60px_rgba(0,0,0,0.18)] backdrop-blur-md sm:p-6">
        <div class="mb-6">
            <a href="{{ url('/admin/registrations') }}" class="text-sm text-neutral-700 hover:underline">← Quay lại danh sách</a>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight">Chỉnh sửa đăng ký #{{ $registration->id }}</h1>
        </div>

        @if($registration->status === 'cancelled')
            <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                Đăng ký này đã bị hủy.
            </div>
        @endif

        <form method="post" action="{{ url('/admin/registrations/'.$registration->id) }}" class="max-w-2xl space-y-6">
            @csrf
            @method('put')

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium" for="full_name">Họ tên</label>
                    <input
                        id="full_name"
                        name="full_name"
                        type="text"
                        value="{{ old('full_name', $registration->full_name) }}"
                        required
                        class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                    />
                </div>

                <div>
                    <label class="text-sm font-medium" for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email', $registration->email) }}"
                        required
                        class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                    />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium" for="phone">SĐT</label>
                    <input
                        id="phone"
                        name="phone"
                        type="text"
                        value="{{ old('phone', $registration->phone) }}"
                        class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                    />
                </div>

                <div>
                    <label class="text-sm font-medium" for="event_session_id">Trình chiếu</label>
                    <select
                        id="event_session_id"
                        name="event_session_id"
                        required
                        class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                    >
                        @foreach ($sessions as $s)
                            <option value="{{ $s->id }}" {{ old('event_session_id', $registration->event_session_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->venue->name }} - {{ $s->starts_at->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-4">
                <div>
                    <label class="text-sm font-medium" for="adult_count">Khách</label>
                    <input
                        id="adult_count"
                        name="adult_count"
                        type="number"
                        min="0"
                        value="{{ old('adult_count', $registration->adult_count) }}"
                        required
                        class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                    />
                </div>

                <div>
                    <label class="text-sm font-medium" for="ntl_count">NTL</label>
                    <input
                        id="ntl_count"
                        name="ntl_count"
                        type="number"
                        min="0"
                        value="{{ old('ntl_count', $registration->ntl_count) }}"
                        required
                        class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                    />
                </div>

                <div>
                    <label class="text-sm font-medium" for="ntl_new_count">NTL mới</label>
                    <input
                        id="ntl_new_count"
                        name="ntl_new_count"
                        type="number"
                        min="0"
                        value="{{ old('ntl_new_count', $registration->ntl_new_count) }}"
                        required
                        class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                    />
                </div>

                <div>
                    <label class="text-sm font-medium" for="child_count">Trẻ em</label>
                    <input
                        id="child_count"
                        name="child_count"
                        type="number"
                        min="0"
                        value="{{ old('child_count', $registration->child_count) }}"
                        required
                        class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                    />
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button class="rounded-2xl bg-neutral-900 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-neutral-800">
                    Lưu thay đổi
                </button>
                <a href="{{ url('/admin/registrations') }}" class="rounded-2xl border border-neutral-300 px-5 py-3 text-sm font-semibold text-neutral-700 hover:bg-neutral-50">
                    Hủy
                </a>
            </div>
        </form>

        @if($registration->status === 'confirmed')
            <div class="mt-8 border-t border-neutral-200 pt-6">
                <h3 class="text-lg font-semibold">Hủy đăng ký</h3>
                <p class="mt-1 text-sm text-neutral-600">Hủy đăng ký sẽ giải phóng chỗ cho trình chiếu này.</p>
                <form method="post" action="{{ url('/admin/registrations/'.$registration->id.'/cancel') }}" class="mt-4">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-2xl border border-red-300 bg-red-50 px-5 py-3 text-sm font-semibold text-red-700 hover:bg-red-100"
                        onclick="return confirm('Bạn có chắc muốn hủy đăng ký này?')"
                    >
                        Hủy đăng ký
                    </button>
                </form>
            </div>
        @endif

        <div class="mt-8 border-t border-neutral-200 pt-6">
            <h3 class="text-lg font-semibold text-red-700">Xóa đăng ký</h3>
            <p class="mt-1 text-sm text-neutral-600">Xóa vĩnh viễn đăng ký này. Hành động này không thể hoàn tác.</p>
            <form method="post" action="{{ url('/admin/registrations/'.$registration->id) }}" class="mt-4">
                @csrf
                @method('delete')
                <button
                    type="submit"
                    class="rounded-2xl border border-red-600 bg-red-700 px-5 py-3 text-sm font-semibold text-white hover:bg-red-800"
                    onclick="return confirm('Bạn có chắc muốn xóa vĩnh viễn đăng ký này? Hành động này không thể hoàn tác.')"
                >
                    Xóa vĩnh viễn
                </button>
            </form>
        </div>
    </div>
@endsection
