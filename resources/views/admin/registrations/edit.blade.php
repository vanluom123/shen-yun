@extends('layouts.app', ['title' => 'Admin – Chỉnh sửa đăng ký'])

@section('content')
    @php $backUrl = request()->query('back') ? urldecode(request()->query('back')) : url('/admin/registrations'); @endphp
    <div class="rounded-3xl border border-white/25 bg-white/65 p-5 shadow-[0_16px_60px_rgba(0,0,0,0.18)] backdrop-blur-md sm:p-6">
        <div class="mb-6">
            <a href="{{ $backUrl }}" class="text-sm text-neutral-700 hover:underline">← Quay lại danh sách</a>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight">Chỉnh sửa đăng ký #{{ $registration->id }}</h1>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 max-w-2xl">
                <div>
                    <div class="text-xs text-neutral-500 uppercase tracking-wide">Ngày đăng ký</div>
                    <div class="mt-1 text-sm font-medium text-neutral-700">{{ $registration->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <div class="text-xs text-neutral-500 uppercase tracking-wide">Trạng thái</div>
                    <div class="mt-1 text-sm">
                        @if($registration->status === 'pending')
                            <span class="font-semibold text-amber-600">⏳ Chờ xác nhận</span>
                        @elseif($registration->status === 'confirmed')
                            <span class="font-semibold text-green-800">✅ Đã xác nhận</span>
                        @else
                            <span class="font-semibold text-red-600">❌ Đã hủy</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @php
            $inputClass = 'mt-2 w-full rounded-xl border bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900';
            $normalBorder = 'border-neutral-300';
            $errorBorder = 'admin-field-invalid';

            $currentSession = $sessions->firstWhere('id', $registration->event_session_id);
            $isPastSession = $currentSession && $currentSession->starts_at->isPast();
            $isCancelled = $registration->status === 'cancelled';
            $isPending = $registration->status === 'pending';
            $shouldDisable = $isPastSession || $isCancelled;
            $disabledAttr = $shouldDisable ? 'disabled' : '';
            $disabledClass = $shouldDisable ? 'bg-neutral-100 opacity-70' : '';
        @endphp
        @if($isPastSession)
            <div class="mb-6 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-800">
                <div class="font-semibold">⚠️ Trình chiếu này đã kết thúc</div>
                Bạn chỉ có thể chỉnh sửa thông tin liên hệ (Họ tên, Email, SĐT). Các thông tin về trình chiếu và số lượng khách đã được khóa.
            </div>
        @else
            @if($isCancelled)
                <div class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <div class="font-semibold">🚫 Đăng ký này đã bị hủy</div>
                    Thông tin trình chiếu và số lượng khách đã bị khóa. Bạn vẫn có thể chỉnh sửa thông tin liên hệ hoặc khôi phục đăng ký bên dưới.
                </div>
            @endif
            @if($isPending || $isCancelled)
                <div class="mb-8 border-b border-neutral-200 pb-6">
                    <h3 class="text-lg font-semibold {{ $isCancelled ? 'text-amber-700' : 'text-green-700' }}">
                        {{ $isCancelled ? 'Khôi phục đăng ký' : 'Xác nhận đăng ký' }}
                    </h3>
                    <p class="mt-1 text-sm text-neutral-600">
                        {{ $isCancelled ? 'Bấm nút bên dưới để đưa đăng ký này trở lại danh sách tham dự.' : 'Bấm nút dưới đây để xác nhận khách tham dự buổi tiệc trà.' }}
                    </p>
                    <form method="post" action="{{ url('/admin/registrations/'.$registration->id.'/confirm') }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}?back={{ urlencode($backUrl) }}">
                        <button type="submit"
                            {{ $isPastSession ? 'disabled' : '' }}
                            class="rounded-xl border {{ $isCancelled ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100' : 'border-green-300 bg-green-50 text-green-700 hover:bg-green-100' }} px-5 py-3 text-sm font-semibold shadow-sm {{ $isPastSession ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                            {{ $isCancelled ? 'Khôi phục' : 'Xác nhận' }}
                        </button>
                    </form>
                </div>
            @endif
        @endif

        <form id="registration-form" method="post" action="{{ url('/admin/registrations/'.$registration->id) }}?back={{ urlencode($backUrl) }}" class="max-w-2xl space-y-6" novalidate>
            @csrf
            @method('put')

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium" for="full_name">Họ tên <span class="text-red-500">*</span></label>
                    <input id="full_name" name="full_name" type="text"
                        value="{{ old('full_name', $registration->full_name) }}" required
                        class="{{ $inputClass }} {{ $errors->has('full_name') ? $errorBorder : $normalBorder }}" />
                </div>

                <div>
                    <label class="text-sm font-medium" for="email">Email</label>
                    <input id="email" name="email" type="email"
                        value="{{ old('email', $registration->email) }}"
                        class="{{ $inputClass }} {{ $errors->has('email') ? $errorBorder : $normalBorder }}" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium">SĐT <span class="text-red-500">*</span></label>
                    @php
                        $phoneRaw = (string) old('phone', $registration->phone ?? '');
                        $parsedCountry = null;
                        $parsedNumber = null;
                        if (preg_match('/^(\+(?:84|1|82|81|65))(\d+)$/', $phoneRaw, $m)) {
                            $parsedCountry = $m[1];
                            $parsedNumber = $m[2];
                        }
                        $phoneCountry = (string) old('phone_country', $parsedCountry ?? '+84');
                        $phoneNumber = (string) old('phone_number', $parsedNumber ?? preg_replace('/\D+/', '', $phoneRaw));
                        $phoneBorder = $errors->hasAny(['phone_number', 'phone_country']) ? $errorBorder : $normalBorder;
                    @endphp
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <select name="phone_country"
                            class="rounded-xl border bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900 {{ $errors->has('phone_country') ? $errorBorder : $normalBorder }}">
                            @foreach(['+84' => '+84 VN', '+1' => '+1 US', '+82' => '+82 KR', '+81' => '+81 JP', '+65' => '+65 SG'] as $val => $label)
                                <option value="{{ $val }}" {{ $phoneCountry === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="tel" name="phone_number" inputmode="numeric" value="{{ $phoneNumber }}"
                            placeholder="Nhập số…" required
                            class="col-span-2 rounded-xl border bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900 {{ $errors->has('phone_number') ? $errorBorder : $normalBorder }}" />
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium" for="event_session_id">Trình chiếu <span class="text-red-500">*</span></label>
                    <select id="event_session_id" name="event_session_id" required
                        {{ $disabledAttr }}
                        class="{{ $inputClass }} {{ $disabledClass }} {{ $errors->has('event_session_id') ? $errorBorder : $normalBorder }}">
                        @foreach ($sessions as $s)
                            <option value="{{ $s->id }}" {{ old('event_session_id', $registration->event_session_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->venue->name }} - {{ $s->starts_at->format('d/m/Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                    @if($shouldDisable)
                        <input type="hidden" name="event_session_id" value="{{ $registration->event_session_id }}">
                    @endif
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-4">
                @foreach([
                    ['adult_count', 'Khách'],
                    ['ntl_count', 'NTL'],
                    ['ntl_new_count', 'NTL mới'],
                    ['child_count', 'Trẻ em'],
                ] as [$field, $label])
                <div>
                    <label class="text-sm font-medium" for="{{ $field }}">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="number" min="0"
                        {{ $disabledAttr }}
                        value="{{ old($field, $registration->$field) }}" required
                        class="{{ $inputClass }} {{ $disabledClass }} {{ $errors->has($field) ? $errorBorder : $normalBorder }}" />
                    @if($shouldDisable)
                        <input type="hidden" name="{{ $field }}" value="{{ $registration->$field }}">
                    @endif
                </div>
                @endforeach
            </div>

            <div class="pt-2">
                <label class="text-sm font-medium">Đi cùng khách</label>
                <div class="mt-3 flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <input id="attend_with_guest_yes" name="attend_with_guest" type="radio" value="1"
                            {{ $disabledAttr }}
                            {{ old('attend_with_guest', $registration->attend_with_guest) == 1 ? 'checked' : '' }}
                            class="h-4 w-4 border-neutral-300 text-neutral-900 focus:ring-neutral-900" />
                        <label for="attend_with_guest_yes" class="text-sm">Có</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="attend_with_guest_no" name="attend_with_guest" type="radio" value="0"
                            {{ $disabledAttr }}
                            {{ old('attend_with_guest', $registration->attend_with_guest) == 0 ? 'checked' : '' }}
                            class="h-4 w-4 border-neutral-300 text-neutral-900 focus:ring-neutral-900" />
                        <label for="attend_with_guest_no" class="text-sm">Không</label>
                    </div>
                </div>
                @if($shouldDisable)
                    <input type="hidden" name="attend_with_guest" value="{{ $registration->attend_with_guest ? '1' : '0' }}">
                @endif
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button type="submit" id="save-button" disabled
                    class="rounded-xl bg-neutral-900 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    Lưu thay đổi
                </button>
                <a href="{{ $backUrl }}" class="rounded-xl border border-neutral-300 px-5 py-3 text-sm font-semibold text-neutral-700 hover:bg-neutral-50">
                    {{ $isPastSession ? 'Quay lại' : 'Hủy' }}
                </a>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('registration-form');
                const saveButton = document.getElementById('save-button');
                
                if (!form || !saveButton) return;

                // Function to get current form state as a URLSearchParams string
                const getFormState = () => new URLSearchParams(new FormData(form)).toString();
                
                // Store initial state
                const initialState = getFormState();

                const updateButtonState = () => {
                    const currentState = getFormState();
                    const hasChanged = currentState !== initialState;
                    
                    saveButton.disabled = !hasChanged;
                };

                // Listen for any input changes
                form.addEventListener('input', updateButtonState);
                form.addEventListener('change', updateButtonState);
            });
        </script>

        @if($registration->status === 'confirmed')
            <div class="mt-8 border-t border-neutral-200 pt-6">
                <h3 class="text-lg font-semibold">Hủy đăng ký</h3>
                <p class="mt-1 text-sm text-neutral-600">Hủy đăng ký sẽ giải phóng chỗ cho trình chiếu này.</p>
                <form method="post" action="{{ url('/admin/registrations/'.$registration->id.'/cancel') }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}?back={{ urlencode($backUrl) }}">
                    <button type="submit"
                        {{ $disabledAttr }}
                        class="rounded-xl border border-red-300 bg-red-50 px-5 py-3 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-100 {{ $isPastSession ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
                        @if(! $isPastSession) onclick="return confirm('Bạn có chắc muốn hủy đăng ký này?')" @endif>
                        Hủy đăng ký
                    </button>
                </form>
            </div>
        @endif

        <div class="mt-8 border-t border-neutral-200 pt-6">
            <h3 class="text-lg font-semibold text-red-700">Xóa đăng ký</h3>
            <p class="mt-1 text-sm text-neutral-600">Xóa người này khỏi danh sách đăng ký.</p>
            <form method="post" action="{{ url('/admin/registrations/'.$registration->id) }}" class="mt-4">
                @csrf
                @method('delete')
                <input type="hidden" name="redirect_to" value="{{ $backUrl }}">
                <button type="submit"
                    {{ $disabledAttr }}
                    class="rounded-xl border admin-field-invalid bg-red-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-800 {{ $isPastSession ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
                    @if(! $isPastSession) onclick="return confirm('Bạn có chắc muốn xóa đăng ký này? Hành động này không thể hoàn tác.')" @endif>
                    Xóa
                </button>
            </form>
        </div>
    </div>
@endsection
