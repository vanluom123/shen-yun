@extends('layouts.app', ['title' => 'Admin – Mẫu lịch chiếu'])

@section('content')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Mẫu lịch chiếu</h1>
            <p class="mt-1 text-sm text-neutral-600">Quản lý mẫu lịch chiếu tự động cho từng địa điểm.</p>
        </div>

        <a
            href="{{ url('/admin/templates/create') }}"
            class="rounded-xl bg-neutral-900 px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800"
        >
            Thêm mẫu lịch
        </a>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-6 overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-neutral-50 text-left text-xs font-semibold text-neutral-600">
                <tr>
                    <th class="px-4 py-3">Địa điểm</th>
                    <th class="px-4 py-3">Số khung giờ</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
                @forelse ($templates as $template)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $template->venue->name }}</td>
                        <td class="px-4 py-3 text-neutral-700">
                            {{ $template->slots->count() }} khung giờ
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a
                                    href="{{ url('/admin/templates/'.$template->id.'/edit') }}"
                                    class="rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-neutral-50"
                                >
                                    Sửa
                                </a>
                                <form method="post" action="{{ url('/admin/templates/'.$template->id) }}" onsubmit="return confirm('Xoá mẫu lịch chiếu này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-rose-300 bg-white px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">
                                        Xoá
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-10 text-center text-neutral-600" colspan="3">Chưa có mẫu lịch chiếu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $templates->links() }}
    </div>
@endsection
