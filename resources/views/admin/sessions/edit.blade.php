@extends('layouts.app', ['title' => 'Admin – Sửa trình chiếu'])

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Sửa trình chiếu</h1>
                <p class="mt-1 text-sm text-neutral-600">{{ $session->venue->name }} •
                    {{ $session->starts_at->format('d/m/Y H:i') }}</p>
                <p class="mt-0.5 text-xs text-neutral-400">Ngày tạo: {{ $session->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <a href="{{ url('/admin/sessions') }}"
                class="rounded-xl border border-neutral-300 bg-white px-4 py-2 text-sm font-medium hover:bg-neutral-50">
                Quay lại
            </a>
        </div>

        <div class="mt-6 rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
            <form method="post" action="{{ url('/admin/sessions/' . $session->id) }}">
                @csrf
                @method('PUT')

                @include('admin.sessions.form', ['session' => $session, 'venues' => $venues])

                @if(!$session->starts_at->isPast())
                    <div class="mt-6 flex justify-end">
                        <button
                            class="rounded-xl bg-neutral-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-neutral-800">
                            Cập nhật
                        </button>
                    </div>
                @else
                    <div
                        class="mt-4 rounded-xl border border-orange-200 bg-orange-50 p-4 text-center text-sm font-medium text-orange-800">
                        <div class="font-semibold">⚠️ Trình chiếu này đã kết thúc</div>
                        Bạn không thể chỉnh sửa thông tin của buổi trình chiếu đã diễn ra.
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection