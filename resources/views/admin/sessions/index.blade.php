@extends('layouts.app', ['title' => 'Admin – trình chiếu'])

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">trình chiếu</h1>
            <p class="mt-1 text-sm text-neutral-600">Quản lý danh sách trình chiếu. Bật <span class="font-medium">Tạm hoãn</span> để ngừng nhận đăng ký tuần đó.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                id="delete-selected"
                class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-medium text-white hover:bg-rose-700 hidden whitespace-nowrap"
                onclick="confirmBulkDelete()"
            >
                Xoá đã chọn
            </button>

            <form method="post" action="{{ url('/admin/sessions/generate') }}" class="inline">
                @csrf
                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 whitespace-nowrap"
                >
                    Tạo trình chiếu
                </button>
            </form>

            <a
                href="{{ url('/admin/sessions/create') }}"
                class="rounded-xl bg-neutral-900 px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800 whitespace-nowrap"
            >
                Thêm trình chiếu
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

    <div class="mt-6 overflow-x-auto rounded-2xl border border-neutral-200 bg-white shadow-sm">
        <table class="w-full min-w-[640px] text-sm">
            <thead class="bg-neutral-50 text-left text-xs font-semibold text-neutral-600">
                    <tr>
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" id="select-all" class="rounded border-neutral-300">
                        </th>
                        <th class="px-4 py-3">trình chiếu</th>
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
                                <form method="post" action="{{ url('/admin/sessions/'.$s->id) }}" onsubmit="return confirm('Xoá trình chiếu này?')">
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
                        <td class="px-4 py-10 text-center text-neutral-600" colspan="5">Chưa có trình chiếu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $sessions->appends(['sessions_page' => $sessions->currentPage()])->links() }}
    </div>

    <div class="mt-12">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight">Mẫu lịch chiếu</h2>
                <p class="mt-1 text-sm text-neutral-600">Lịch chiếu mẫu hàng tuần cho từng địa điểm. Hệ thống sử dụng mẫu này để tự động tạo trình chiếu mới.</p>
            </div>

            <a
                href="{{ url('/admin/templates/create') }}"
                class="rounded-xl bg-neutral-900 px-4 py-2 text-sm font-medium text-white hover:bg-neutral-800 whitespace-nowrap"
            >
                Thêm mẫu lịch
            </a>
        </div>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-neutral-200 bg-white shadow-sm">
            <table class="w-full min-w-[640px] text-sm">
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
            if (confirm('Xoá ' + count + ' trình chiếu đã chọn? Tất cả đăng ký liên quan cũng sẽ bị xoá.')) {
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
