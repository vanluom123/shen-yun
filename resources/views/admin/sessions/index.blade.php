@extends('layouts.app', ['title' => 'Trình chiếu'])

@section('content')
    <div class="space-y-10">
        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-on-surface mb-2">Trình chiếu</h1>
                <p class="text-on-surface-variant max-w-md">Quản lý và theo dõi các chương trình trình chiếu tại các điểm sự kiện. Bật <span class="font-medium text-amber-600">Tạm hoãn</span> để ngừng nhận đăng ký.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    id="delete-selected"
                    class="px-6 py-2.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-xl text-sm font-bold active:scale-95 transition-all hidden items-center gap-2"
                    onclick="confirmBulkDelete()"
                >
                    <span class="material-symbols-outlined text-sm">delete_sweep</span>
                    Xoá đã chọn
                </button>

                <form method="post" action="{{ url('/admin/sessions/generate') }}" class="inline">
                    @csrf
                    <button type="submit" class="btn-admin-primary">
                        <span class="material-symbols-outlined text-sm">auto_videocam</span>
                        Tạo trình chiếu
                    </button>
                </form>
            </div>
        </div>

        <form method="post" id="bulk-delete-form" action="{{ url('/admin/sessions/bulk-destroy') }}" class="hidden">
            @csrf
            @method('DELETE')
            <div id="bulk-delete-inputs"></div>
        </form>

        <!-- Collapsible Accordion Sections -->
        <div class="border border-outline-variant/30 rounded-xl bg-white overflow-hidden shadow-sm">
            
            <!-- Slideshow Template (Mẫu lịch chiếu) Section -->
            <div class="border-b border-outline-variant/30">
                <button class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors focus:outline-none" onclick="toggleCollapse('template-content', 'template-icon')">
                    <div class="flex items-center gap-3">
                        <span id="template-icon" class="material-symbols-outlined text-on-surface-variant transition-transform duration-200">navigate_next</span>
                        <h2 class="text-base font-bold tracking-tight text-on-surface">Mẫu lịch chiếu</h2>
                    </div>
                    <!-- Right Actions -->
                    <div onclick="event.stopPropagation()">
                        <a href="{{ url('/admin/templates/create') }}" class="text-primary text-sm font-semibold hover:underline flex items-center gap-1 bg-primary/5 px-3 py-1.5 rounded-lg transition-colors hover:bg-primary/10">
                            <span class="material-symbols-outlined text-sm">add</span>
                            Thêm mẫu
                        </a>
                    </div>
                </button>
                <div id="template-content" class="hidden border-t border-outline-variant/20 bg-slate-50/50 p-6">
                    <div class="admin-card !shadow-none ring-1 ring-outline-variant/20 bg-white">
                        <div class="overflow-x-auto">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Địa điểm</th>
                                        <th class="text-center">Số khung giờ</th>
                                        <th class="text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-0">
                                    @forelse ($templates as $template)
                                        <tr class="group">
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-lg bg-surface-container-high flex items-center justify-center">
                                                        <span class="material-symbols-outlined text-on-surface-variant">apartment</span>
                                                    </div>
                                                    <span class="font-semibold text-on-surface whitespace-nowrap">{{ $template->venue->name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-sm font-medium whitespace-nowrap">{{ $template->slots->count() }} khung giờ</span>
                                            </td>
                                            <td>
                                                <div class="flex items-center justify-end gap-2 transition-opacity">
                                                    <a
                                                        href="{{ url('/admin/templates/'.$template->id.'/edit') }}"
                                                        class="p-2 hover:bg-primary/10 text-primary rounded-lg transition-colors"
                                                        title="Sửa"
                                                    >
                                                        <span class="material-symbols-outlined text-lg">edit</span>
                                                    </a>
                                                    <form method="post" action="{{ url('/admin/templates/'.$template->id) }}" onsubmit="return confirm('Xoá mẫu lịch chiếu này?')" class="inline">
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
                                            <td class="py-10 text-center text-on-surface-variant" colspan="3">Chưa có mẫu lịch chiếu.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sessions (Trình chiếu) Section -->
            <div>
                <button class="w-full px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition-colors focus:outline-none" onclick="toggleCollapse('sessions-content', 'sessions-icon')">
                    <div class="flex items-center gap-3">
                        <span id="sessions-icon" class="material-symbols-outlined text-on-surface-variant transition-transform duration-200 rotate-90">navigate_next</span>
                        <h2 class="text-base font-bold tracking-tight text-on-surface">Danh sách trình chiếu</h2>
                    </div>
                </button>
                <div id="sessions-content" class="block border-t border-outline-variant/20 bg-slate-50/50 p-6">
                    <div class="admin-card !shadow-none ring-1 ring-outline-variant/20 bg-white">
                        <div class="overflow-x-auto">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th class="w-12 text-center">
                                            <input type="checkbox" id="select-all" class="rounded-sm border-outline-variant text-primary focus:ring-primary/40">
                                        </th>
                                        <th>trình chiếu</th>
                                        <th>Capacity</th>
                                        <th>Trạng thái</th>
                                        <th class="text-right">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-0">
                                    @forelse ($sessions as $s)
                                        @php
                                            $remaining = max(0, $s->capacity_total - $s->capacity_reserved);
                                            $isExpired = $s->starts_at->isPast();

                                            $statusStyles = [
                                                'open' => 'bg-emerald-100 text-emerald-700',
                                                'paused' => 'bg-amber-100 text-amber-700',
                                                'hidden' => 'bg-neutral-100 text-neutral-600',
                                            ];
                                            $statusDots = [
                                                'open' => 'bg-emerald-500',
                                                'paused' => 'bg-amber-500',
                                                'hidden' => 'bg-neutral-400',
                                            ];
                                            $statusLabels = [
                                                'open' => 'Hoạt động',
                                                'paused' => 'Tạm hoãn',
                                                'hidden' => 'Ẩn',
                                            ];

                                            if ($isExpired) {
                                                $style = 'bg-orange-100 text-orange-700';
                                                $dot = 'bg-orange-500';
                                                $label = 'Hết hạn';
                                            } else {
                                                $style = $statusStyles[$s->registration_status] ?? 'bg-neutral-100 text-neutral-800';
                                                $dot = $statusDots[$s->registration_status] ?? 'bg-neutral-400';
                                                $label = $statusLabels[$s->registration_status] ?? $s->registration_status;
                                            }
                                        @endphp
                                        <tr class="group {{ $s->isHidden() ? 'opacity-60 saturate-0' : '' }}">
                                            <td class="text-center">
                                                <input type="checkbox" name="session_ids[]" value="{{ $s->id }}" class="session-checkbox rounded-sm border-outline-variant text-primary focus:ring-primary/40">
                                            </td>
                                            <td>
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-on-surface">{{ $s->venue->name }}</span>
                                                    <span class="text-xs text-on-surface-variant">{{ $s->starts_at->format('d/m/Y • H:i') }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-medium">{{ $s->capacity_reserved }} / {{ $s->capacity_total }}</span>
                                                    <span class="text-xs px-2 py-0.5 bg-surface-container rounded-xl text-on-surface-variant">Còn {{ $remaining }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge-status {{ $style }}">
                                                    <span class="w-1.5 h-1.5 rounded-xl {{ $dot }} mr-2"></span>
                                                    {{ $label }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="flex items-center justify-end gap-2 transition-opacity">
                                                    <a
                                                        href="{{ url('/admin/sessions/'.$s->id.'/edit') }}"
                                                        class="p-2 hover:bg-primary/10 text-primary rounded-lg transition-colors"
                                                        title="Sửa"
                                                    >
                                                        <span class="material-symbols-outlined text-lg">edit</span>
                                                    </a>
                                                    <form method="post" action="{{ url('/admin/sessions/'.$s->id) }}" onsubmit="return confirm('Xoá trình chiếu này?')" class="inline">
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
                                            <td class="py-10 text-center text-on-surface-variant" colspan="5">Chưa có trình chiếu.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($sessions->hasPages())
                        <div class="mt-4">
                            {{ $sessions->appends(['sessions_page' => $sessions->currentPage()])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleCollapse(contentId, iconId) {
            const content = document.getElementById(contentId);
            const icon = document.getElementById(iconId);
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                content.classList.add('block');
                icon.classList.add('rotate-90');
            } else {
                content.classList.remove('block');
                content.classList.add('hidden');
                icon.classList.remove('rotate-90');
            }
        }

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
