@php
    $template = $template ?? null;
    $venues = $venues ?? [];
@endphp

<div class="space-y-6">
    <div>
        <label class="text-sm font-medium" for="venue_id">Địa điểm</label>
        <select
            id="venue_id"
            name="venue_id"
            required
            class="mt-2 w-full rounded-xl border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
            {{ $template ? 'disabled' : '' }}
        >
            <option value="">-- Chọn địa điểm --</option>
            @foreach ($venues as $venue)
                <option value="{{ $venue->id }}" {{ old('venue_id', $template?->venue_id) == $venue->id ? 'selected' : '' }}>
                    {{ $venue->name }}
                </option>
            @endforeach
        </select>
        @if ($template)
            <input type="hidden" name="venue_id" value="{{ $template->venue_id }}">
        @endif
        @error('venue_id')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <div class="flex items-center justify-between">
            <label class="text-sm font-medium">Khung giờ chiếu</label>
            <button type="button" id="addSlot" class="rounded-lg border border-neutral-300 bg-white px-3 py-1.5 text-xs font-medium hover:bg-neutral-50">
                + Thêm khung giờ
            </button>
        </div>

        <div id="slotsContainer" class="mt-3 space-y-3">
            @php
                $oldSlots = old('slots', $template?->slots->toArray() ?? []);
                if (empty($oldSlots)) {
                    $oldSlots = [['day_of_week' => '', 'time' => '', 'default_capacity' => '']];
                }
            @endphp

            @foreach ($oldSlots as $index => $slot)
                <div class="slot-row flex gap-3 items-start">
                    <div class="flex-1">
                        <select name="slots[{{ $index }}][day_of_week]" required class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900">
                            <option value="">-- Ngày --</option>
                            <option value="0" {{ ($slot['day_of_week'] ?? '') == '0' ? 'selected' : '' }}>Chủ nhật</option>
                            <option value="1" {{ ($slot['day_of_week'] ?? '') == '1' ? 'selected' : '' }}>Thứ hai</option>
                            <option value="2" {{ ($slot['day_of_week'] ?? '') == '2' ? 'selected' : '' }}>Thứ ba</option>
                            <option value="3" {{ ($slot['day_of_week'] ?? '') == '3' ? 'selected' : '' }}>Thứ tư</option>
                            <option value="4" {{ ($slot['day_of_week'] ?? '') == '4' ? 'selected' : '' }}>Thứ năm</option>
                            <option value="5" {{ ($slot['day_of_week'] ?? '') == '5' ? 'selected' : '' }}>Thứ sáu</option>
                            <option value="6" {{ ($slot['day_of_week'] ?? '') == '6' ? 'selected' : '' }}>Thứ bảy</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <input
                            type="time"
                            name="slots[{{ $index }}][time]"
                            value="{{ $slot['time'] ?? '' }}"
                            required
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                        />
                    </div>
                    <div class="w-32">
                        <input
                            type="number"
                            name="slots[{{ $index }}][default_capacity]"
                            value="{{ $slot['default_capacity'] ?? '' }}"
                            placeholder="Sức chứa"
                            min="1"
                            required
                            class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                        />
                    </div>
                    <button type="button" class="removeSlot rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                        Xóa
                    </button>
                </div>
            @endforeach
        </div>

        @error('slots')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let slotIndex = {{ count($oldSlots) }};
    const container = document.getElementById('slotsContainer');
    const addButton = document.getElementById('addSlot');

    addButton.addEventListener('click', function() {
        const slotRow = document.createElement('div');
        slotRow.className = 'slot-row flex gap-3 items-start';
        slotRow.innerHTML = `
            <div class="flex-1">
                <select name="slots[${slotIndex}][day_of_week]" required class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900">
                    <option value="">-- Ngày --</option>
                    <option value="0">Chủ nhật</option>
                    <option value="1">Thứ hai</option>
                    <option value="2">Thứ ba</option>
                    <option value="3">Thứ tư</option>
                    <option value="4">Thứ năm</option>
                    <option value="5">Thứ sáu</option>
                    <option value="6">Thứ bảy</option>
                </select>
            </div>
            <div class="flex-1">
                <input
                    type="time"
                    name="slots[${slotIndex}][time]"
                    required
                    class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                />
            </div>
            <div class="w-32">
                <input
                    type="number"
                    name="slots[${slotIndex}][default_capacity]"
                    placeholder="Sức chứa"
                    min="1"
                    required
                    class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900"
                />
            </div>
            <button type="button" class="removeSlot rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-100">
                Xóa
            </button>
        `;
        container.appendChild(slotRow);
        slotIndex++;
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('removeSlot')) {
            const slotRows = container.querySelectorAll('.slot-row');
            if (slotRows.length > 1) {
                e.target.closest('.slot-row').remove();
            } else {
                alert('Phải có ít nhất một khung giờ chiếu.');
            }
        }
    });
});
</script>
