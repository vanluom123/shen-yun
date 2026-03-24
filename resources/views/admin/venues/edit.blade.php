@extends('layouts.app', ['title' => 'Admin – Sửa địa điểm'])

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Sửa địa điểm</h1>
                <p class="mt-1 text-sm text-neutral-600">{{ $venue->name }}</p>
            </div>
            <a href="{{ url('/admin/venues') }}" class="rounded-xl border border-neutral-300 bg-white px-4 py-2 text-sm font-medium hover:bg-neutral-50">
                Quay lại
            </a>
        </div>

        <div class="mt-6 rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
            <form method="post" action="{{ url('/admin/venues/'.$venue->id) }}">
                @csrf
                @method('PUT')

                @include('admin.venues.form', ['venue' => $venue])

                <div class="mt-6 flex justify-end">
                    <button class="rounded-xl bg-neutral-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-neutral-800">
                        Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

