@extends('layouts.app', ['title' => 'Admin – Suất diễn'])

@section('content')
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Suất diễn</h1>
            <p class="mt-1 text-sm text-neutral-600">Quản lý danh sách suất diễn. Bật <span class="font-medium">Tạm hoãn</span> để ngừng nhận đăng ký tuần đó.</p>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                id="delete-selected"
                class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 hidden"
                onclick="confirmBulkDelete()"
            >
                Xoá đã chọn
            </button>

            <a
                href="{{ url('/admin/sessions/create') }}"
                class="rounded-xl bg-neutral-900 px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800"
            >
                Thêm suất diễn
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="post" id="bulk-delete-form" action="{{ url('/admin/sessions/bulk-destroy') }}">
        @csrf
        @method('DELETE')

        <div class="mt-6 overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-neutral-50 text-left text-xs font-semibold text-neutral-600">
                    <tr>
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" id="select-all" class="rounded border-neutral-300">
                        </th>
                        <th class="px-4 py-3">Suất diễn</th>
                        <th class="px-4 py-3">Capacity</th>
                        <th class="px-4 py-3">Trạng thái</th>
                        <th class="px-4 py-3">Nhận đăng ký</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
            <tbody class="divide-y divide-neutral-200">
                @forelse ($sessions as $s)
                    @php
                        $remaining = max(0, $s->capacity_total - $s->capacity_reserved);
                    @endphp
                        <tr class="{{ $s->is_registration_blocked ? 'bg-amber-50' : '' }}">
                            <td class="px-4 py-3">
                                <input type="checkbox" name="session_ids[]" value="{{ $s->id }}" class="session-checkbox rounded border-neutral-300">
                            </td>
                            <td class="px-4 py-3">
                            <div class="font-medium">{{ $s->venue->name }}</div>
                            <div class="text-xs text-neutral-600">{{ $s->starts_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-4 py-3 text-neutral-700">
                            {{ $s->capacity_reserved }} / {{ $s->capacity_total }}
                            <div class="text-xs text-neutral-500">Còn {{ $remaining }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-800">
                                {{ $s->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($s->is_registration_blocked)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524L13.477 14.89zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/></svg>
                                    Tạm hoãn
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Đang mở
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <form method="post" action="{{ url('/admin/sessions/'.$s->id.'/toggle-block') }}">
                                    @csrf
                                    @if ($s->is_registration_blocked)
                                        <button
                                            class="rounded-lg border border-emerald-300 bg-white px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-50"
                                            title="Mở lại nhận đăng ký"
                                        >
                                            Mở lại
                                        </button>
                                    @else
                                        <button
                                            class="rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-50"
                                            onclick="return confirm('Tạm hoãn nhận đăng ký cho suất này?')"
                                            title="Tạm hoãn nhận đăng ký"
                                        >
                                            Tạm hoãn
                                        </button>
                                    @endif
                                </form>

                                <a
                                    href="{{ url('/admin/sessions/'.$s->id.'/edit') }}"
                                    class="rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-neutral-50"
                                >
                                    Sửa
                                </a>
                                <form method="post" action="{{ url('/admin/sessions/'.$s->id) }}" onsubmit="return confirm('Xoá suất diễn này?')">
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
                        <td class="px-4 py-10 text-center text-neutral-600" colspan="6">Chưa có suất diễn.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </form>

    <div class="mt-4">
        {{ $sessions->links() }}
    </div>

    <script>
        document.getElementById('select-all').addEventListener('change', function(e) {
            document.querySelectorAll('.session-checkbox').forEach(cb => cb.checked = e.target.checked);
            updateDeleteButton();
        });

        document.querySelectorAll('.session-checkbox').forEach(cb => {
            cb.addEventListener('change', updateDeleteButton);
        });

        function updateDeleteButton() {
            const checked = document.querySelectorAll('.session-checkbox:checked').length;
            const btn = document.getElementById('delete-selected');
            if (checked > 0) {
                btn.classList.remove('hidden');
                btn.textContent = 'Xoá đã chọn (' + checked + ')';
            } else {
                btn.classList.add('hidden');
            }
        }

        function confirmBulkDelete() {
            const count = document.querySelectorAll('.session-checkbox:checked').length;
            if (confirm('Xoá ' + count + ' suất diễn đã chọn? Tất cả đăng ký liên quan cũng sẽ bị xoá.')) {
                document.getElementById('bulk-delete-form').submit();
            }
        }
    </script>
@endsection
