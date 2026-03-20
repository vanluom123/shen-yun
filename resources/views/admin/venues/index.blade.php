@extends('layouts.app', ['title' => 'Địa điểm'])

@section('content')
    <div class="space-y-10">
        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-on-surface mb-2">Địa điểm</h1>
                <p class="text-on-surface-variant max-w-md">Danh sách các địa điểm tổ chức sự kiện. Bạn có thể cài đặt múi giờ và địa chỉ cho từng địa điểm.</p>
            </div>

            <a
                href="{{ url('/admin/venues/create') }}"
                class="btn-admin-primary px-8"
            >
                <span class="material-symbols-outlined text-sm">add</span>
                Thêm địa điểm
            </a>
        </div>

        <!-- Venues Table Card -->
        <div class="admin-card">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tên địa điểm</th>
                            <th>Địa chỉ</th>
                            <th>Múi giờ</th>
                            <th class="text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-0">
                        @forelse ($venues as $v)
                            <tr class="group">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-primary/5 flex items-center justify-center text-primary">
                                            <span class="material-symbols-outlined text-xl">location_on</span>
                                        </div>
                                        <span class="font-bold text-on-surface">{{ $v->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm text-on-surface-variant">{{ $v->address ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="px-3 py-1 bg-surface-container rounded-full text-xs font-medium text-on-surface-variant">
                                        {{ $v->timezone }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a
                                            href="{{ url('/admin/venues/'.$v->id.'/edit') }}"
                                            class="p-2 hover:bg-primary/10 text-primary rounded-lg transition-colors"
                                            title="Sửa"
                                        >
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        <form method="post" action="{{ url('/admin/venues/'.$v->id) }}" onsubmit="return confirm('Xoá địa điểm này?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="p-2 hover:bg-error/10 text-error rounded-lg transition-colors" title="Xoá">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
@empty
                            <tr>
                                <td class="py-10 text-center text-on-surface-variant" colspan="4">Chưa có địa điểm.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($venues->hasPages())
            <div class="mt-4">
                {{ $venues->links() }}
            </div>
        @endif
    </div>
@endsection

