@php
    $session = $session ?? null;
@endphp

<div class="space-y-4">
    <div>
        <label class="text-sm font-medium" for="venue_id">Địa điểm</label>
        <select
            id="venue_id"
            name="venue_id"
            required
            class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
        >
            <option value="" disabled {{ empty(old('venue_id', $session?->venue_id ?? $default_venue_id ?? null)) ? 'selected' : '' }}>Chọn địa điểm…</option>
            @foreach ($venues as $v)
                <option value="{{ $v->id }}" {{ (string) old('venue_id', $session?->venue_id ?? $default_venue_id ?? null) === (string) $v->id ? 'selected' : '' }}>
                    {{ $v->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-sm font-medium" for="starts_at">Bắt đầu (datetime)</label>
        <input
            id="starts_at"
            name="starts_at"
            type="datetime-local"
            value="{{ old('starts_at', $session?->starts_at?->format('Y-m-d\TH:i') ?? $default_starts_at ?? '') }}"
            required
            class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
        />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="text-sm font-medium" for="capacity_total">Số lượng ghế</label>
            <input
                id="capacity_total"
                name="capacity_total"
                type="number"
                min="1"
                value="{{ old('capacity_total', $session?->capacity_total ?? $default_capacity ?? 36) }}"
                required
                class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
            />
        </div>

        <div>
            <label class="text-sm font-medium" for="registration_status">Trạng thái</label>
            <select
                id="registration_status"
                name="registration_status"
                required
                class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
            >
                @foreach (['open' => 'Hoạt động', 'paused' => 'Tạm hoãn', 'hidden' => 'Ẩn'] as $val => $label)
                    <option value="{{ $val }}" {{ old('registration_status', $session?->registration_status ?? 'open') === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($session)
        <div class="rounded-xl border border-neutral-200 bg-neutral-50 p-4 text-sm text-neutral-700">
            Đã giữ chỗ: <span class="font-semibold">{{ $session->capacity_reserved }}</span>
        </div>
    @endif
</div>

