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

    <form method="post" id="bulk-delete-form" action="{{ url('/admin/sessions/bulk-destroy') }}" class="hidden">
        @csrf
        @method('DELETE')
        <div id="bulk-delete-inputs"></div>
    </form>

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
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
            <tbody class="divide-y divide-neutral-200">
                @forelse ($sessions as $s)
                    @php
                        $remaining = max(0, $s->capacity_total - $s->capacity_reserved);
                        $statusColors = [
                            'open' => 'bg-emerald-100 text-emerald-800',
                            'paused' => 'bg-amber-100 text-amber-800',
                            'hidden' => 'bg-neutral-100 text-neutral-600',
                        ];
                        $statusLabels = [
                            'open' => 'Hoạt động',
                            'paused' => 'Tạm hoãn',
                            'hidden' => 'Ẩn',
                        ];
                        $color = $statusColors[$s->registration_status] ?? 'bg-neutral-100 text-neutral-800';
                        $label = $statusLabels[$s->registration_status] ?? $s->registration_status;
                    @endphp
                        <tr class="{{ $s->isHidden() ? 'bg-neutral-50 opacity-60' : '' }}">
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
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $color }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
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
                        <td class="px-4 py-10 text-center text-neutral-600" colspan="5">Chưa có suất diễn.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

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
            const checkboxes = document.querySelectorAll('.session-checkbox:checked');
            const count = checkboxes.length;
            if (confirm('Xoá ' + count + ' suất diễn đã chọn? Tất cả đăng ký liên quan cũng sẽ bị xoá.')) {
                const form = document.getElementById('bulk-delete-form');
                const inputsContainer = document.getElementById('bulk-delete-inputs');
                inputsContainer.innerHTML = '';
                checkboxes.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'session_ids[]';
                    input.value = cb.value;
                    inputsContainer.appendChild(input);
                });
                form.submit();
            }
        }
    </script>
@endsection
