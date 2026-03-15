@extends('layouts.app', ['title' => 'Admin – Đăng ký'])

@section('content')
    @if(session('success'))
        <div class="mb-4 rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-3xl border border-white/25 bg-white/65 shadow-[0_16px_60px_rgba(0,0,0,0.18)] backdrop-blur-md">
        <div class="px-5 sm:px-6 pt-5 sm:pt-6 pb-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">Danh sách khách đã đăng ký</h1>
                    <p class="mt-1 text-sm text-neutral-700/80">Xem và xuất file Excel (CSV).</p>
                </div>

                <a
                    href="{{ url('/admin/registrations/export.xls') }}?status={{ $statusFilter }}&session_id={{ $sessionIdFilter }}"
                    class="inline-flex items-center justify-center rounded-3xl bg-[#1a1a1a] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-neutral-800"
                >
                    Xuất Excel
                </a>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <select
                        id="sessionFilter"
                        class="w-auto min-w-[200px] appearance-none rounded-full border border-neutral-200 bg-white py-2 pl-9 pr-8 text-sm text-neutral-700 outline-none hover:bg-neutral-50 focus:border-neutral-300"
                        onchange="applyFilters()"
                    >
                        <option value="">Suất diễn: Tất cả</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ $sessionIdFilter == $session->id ? 'selected' : '' }}>
                                Suất diễn: {{ $session->starts_at->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 0H4.5M13.5 12h6.75M13.5 12a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm0 0H4.5m0 6h15" />
                        </svg>
                    </div>
                    <select
                        id="statusFilter"
                        class="w-auto min-w-[180px] appearance-none rounded-full border border-neutral-200 bg-white py-2 pl-9 pr-8 text-sm text-neutral-700 outline-none hover:bg-neutral-50 focus:border-neutral-300"
                        onchange="applyFilters()"
                    >
                        <option value="" {{ empty($statusFilter) ? 'selected' : '' }}>Trạng thái: Tất cả</option>
                        <option value="confirmed" {{ $statusFilter === 'confirmed' ? 'selected' : '' }}>Trạng thái: Đã xác nhận</option>
                        <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Trạng thái: Đã hủy</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-white/35 bg-white/70 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full w-full text-sm">
                <thead class="bg-white/70 text-left text-xs font-semibold text-neutral-700">
                    <tr class="[&>th]:px-5 [&>th]:py-4">
                        <th class="w-20 pl-6">Mã</th>
                        <th class="w-56">Người mời</th>
                        <th class="w-72">Gmail</th>
                        <th class="w-40">SĐT</th>
                        <th class="w-36">Suất diễn</th>
                        <th class="w-24">Khách</th>
                        <th class="w-24">NTL</th>
                        <th class="w-28">NTL mới</th>
                        <th class="w-24">Trẻ em</th>
                        <th class="w-24">Tổng</th>
                        <th class="w-24">Trạng thái</th>
                        <th class="w-36">Tạo lúc</th>
                        <th class="w-20 pr-6"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200/70">
                    @forelse ($registrations as $r)
                        <tr class="hover:bg-black/3">
                            <td class="pl-6 py-4 font-mono text-xs text-neutral-700">#{{ $r->id }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ url('/admin/registrations/'.$r->id.'/edit') }}" class="block min-h-[44px] min-w-[44px] flex items-center font-semibold text-neutral-900 hover:underline">
                                    {{ $r->full_name }}
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-neutral-900">{{ $r->email }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($r->phone)
                                    <details class="phone-dropdown relative">
                                        <summary class="list-none cursor-pointer text-neutral-900 hover:underline">
                                            {{ $r->phone }}
                                        </summary>
                                        <div class="absolute z-10 mt-1 w-24 rounded-lg border border-neutral-200 bg-white py-1 shadow-lg">
                                            <a href="tel:{{ $r->phone }}" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100">Call</a>
                                            <a href="https://zalo.me/{{ $r->phone }}" target="_blank" class="block px-4 py-2 text-sm text-neutral-700 hover:bg-neutral-100">Zalo</a>
                                        </div>
                                    </details>
                                @else
                                    <div class="text-neutral-900">—</div>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-semibold text-neutral-900">{{ $r->eventSession->starts_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-neutral-900">{{ $r->adult_count }}</td>
                            <td class="px-5 py-4 font-semibold text-neutral-900">{{ $r->ntl_count }}</td>
                            <td class="px-5 py-4 font-semibold text-neutral-900">{{ $r->ntl_new_count }}</td>
                            <td class="px-5 py-4 font-semibold text-neutral-900">{{ $r->child_count }}</td>
                            <td class="px-5 py-4 font-semibold text-neutral-900">{{ $r->total_count }}</td>
                            <td class="px-5 py-4">
                                @if($r->status === 'confirmed')
                                    <span class="text-lg" title="Đã xác nhận">✅</span>
                                @else
                                    <span class="text-lg" title="Đã hủy">❌</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-neutral-800 whitespace-nowrap">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                            <td class="pr-6 py-4">
                                <a href="{{ url('/admin/registrations/'.$r->id.'/edit') }}" class="text-sm text-neutral-700 hover:underline">Sửa</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-12 text-center text-sm text-neutral-700/80" colspan="12">
                                Chưa có đăng ký.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $registrations->links() }}
    </div>

    <script>
        let autoCloseTimer = null;

        function startAutoCloseTimer(details) {
            clearTimeout(autoCloseTimer);
            autoCloseTimer = setTimeout(() => {
                details.open = false;
            }, 3000);
        }

        function clearAutoCloseTimer() {
            clearTimeout(autoCloseTimer);
        }

        document.querySelectorAll('details.phone-dropdown').forEach(details => {
            details.addEventListener('toggle', function() {
                if (this.open) {
                    document.querySelectorAll('details.phone-dropdown').forEach(other => {
                        if (other !== this) other.open = false;
                    });
                    startAutoCloseTimer(this);
                } else {
                    clearAutoCloseTimer();
                }
            });
        });

        document.addEventListener('click', function(event) {
            const isClickInside = event.target.closest('details.phone-dropdown');
            if (!isClickInside) {
                setTimeout(() => {
                    document.querySelectorAll('details.phone-dropdown[open]').forEach(details => {
                        details.open = false;
                    });
                }, 200);
            } else {
                const openDetails = document.querySelector('details.phone-dropdown[open]');
                if (openDetails) {
                    startAutoCloseTimer(openDetails);
                }
            }
        });

        function applyFilters() {
            const session = document.getElementById('sessionFilter').value;
            const status = document.getElementById('statusFilter').value;
            let url = new URL('{{ url("/admin/registrations") }}');
            if (session) url.searchParams.set('session_id', session);
            if (status) url.searchParams.set('status', status);
            window.location.href = url.toString();
        }
    </script>
@endsection

