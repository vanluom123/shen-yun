@extends('layouts.app', ['title' => 'Admin – Thêm mẫu lịch chiếu'])

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Thêm mẫu lịch chiếu</h1>
                <p class="mt-1 text-sm text-neutral-600">Tạo mẫu lịch chiếu mới cho địa điểm.</p>
            </div>
            <a href="{{ url('/admin/sessions') }}" class="rounded-xl border border-neutral-300 bg-white px-4 py-2 text-sm font-medium hover:bg-neutral-50">
                Quay lại
            </a>
        </div>

        <div class="mt-6 rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">Có lỗi xảy ra:</p>
                    <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ url('/admin/templates') }}" id="templateForm">
                @csrf
                @include('admin.templates.form')

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-xl bg-neutral-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-neutral-800">
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
