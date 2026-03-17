<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EventSession;
use App\Models\Registration;
use App\Models\Venue;
use App\Mail\RegistrationConfirmed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class RegistrationWizardController extends Controller
{
    private const DRAFT_KEY = 'registration_draft_v1';
    private const DEFAULT_VENUE_NAME = 'NX-LP 96 vinhomes grand park';
    private const EVENT_TZ = 'Asia/Ho_Chi_Minh';
    private const EVENT_HOUR = 14;
    private const EVENT_MINUTE = 30;
    private const CUTOFF_HOUR = 22;
    private const CUTOFF_MINUTE = 30;

    private function upcomingSundayStart(Carbon $now): Carbon
    {
        $start = $now->copy();
        if ($start->dayOfWeek !== Carbon::SUNDAY) {
            $start = $start->next(Carbon::SUNDAY);
        }

        $start = $start->setTime(self::EVENT_HOUR, self::EVENT_MINUTE, 0);

        // Cutoff: Saturday 22:30 (after this time -> roll to next Sunday)
        $cutoff = $start->copy()->subDay()->setTime(self::CUTOFF_HOUR, self::CUTOFF_MINUTE, 0);
        if ($now->greaterThanOrEqualTo($cutoff)) {
            $start = $start->addWeek();
        }

        return $start;
    }

    /**
     * Ensure the two upcoming Sunday sessions exist and return the active (unblocked) one,
     * or null if both sessions are admin-blocked.
     */
    private function ensureSingleActiveSundaySession(int $venueId): ?EventSession
    {
        $now = Carbon::now(self::EVENT_TZ);
        // Base Sunday (this upcoming Sunday; today if already Sunday)
        $baseSunday = $now->copy();
        if ($baseSunday->dayOfWeek !== Carbon::SUNDAY) {
            $baseSunday = $baseSunday->next(Carbon::SUNDAY);
        }
        $baseSunday = $baseSunday->setTime(self::EVENT_HOUR, self::EVENT_MINUTE, 0);
        $nextSunday = $baseSunday->copy()->addWeek();

        // Cutoff: Saturday 22:30 (after this time -> roll to next Sunday)
        $cutoff = $baseSunday->copy()->subDay()->setTime(self::CUTOFF_HOUR, self::CUTOFF_MINUTE, 0);
        $preferNext = $now->greaterThanOrEqualTo($cutoff);

        // Find sessions on the same day (in event TZ) so we can "snap" time without breaking existing registrations.
        $dayStart = $baseSunday->copy()->startOfDay();
        $dayEnd = $baseSunday->copy()->endOfDay();
        $nextDayStart = $nextSunday->copy()->startOfDay();
        $nextDayEnd = $nextSunday->copy()->endOfDay();

        $baseSession = EventSession::query()
            ->where('venue_id', $venueId)
            ->whereBetween('starts_at', [$dayStart, $dayEnd])
            ->orderBy('starts_at')
            ->first();

        $nextSession = EventSession::query()
            ->where('venue_id', $venueId)
            ->whereBetween('starts_at', [$nextDayStart, $nextDayEnd])
            ->orderBy('starts_at')
            ->first();

        // Snap/create base session (this week)
        if ($baseSession) {
            if ($baseSession->starts_at->copy()->timezone(self::EVENT_TZ)->toDateTimeString() !== $baseSunday->toDateTimeString()) {
                EventSession::query()->whereKey($baseSession->id)->update(['starts_at' => $baseSunday->toDateTimeString()]);
                $baseSession->starts_at = $baseSunday;
            }
        } else {
            $baseSession = EventSession::query()->create([
                'venue_id' => $venueId,
                'starts_at' => $baseSunday->toDateTimeString(),
                'capacity_total' => 36,
                'capacity_reserved' => 0,
                'registration_status' => 'hidden',
            ]);
        }

        // Snap/create next session (next week)
        if ($nextSession) {
            if ($nextSession->starts_at->copy()->timezone(self::EVENT_TZ)->toDateTimeString() !== $nextSunday->toDateTimeString()) {
                EventSession::query()->whereKey($nextSession->id)->update(['starts_at' => $nextSunday->toDateTimeString()]);
                $nextSession->starts_at = $nextSunday;
            }
        } else {
            $nextSession = EventSession::query()->create([
                'venue_id' => $venueId,
                'starts_at' => $nextSunday->toDateTimeString(),
                'capacity_total' => 36,
                'capacity_reserved' => 0,
                'registration_status' => 'hidden',
            ]);
        }

        // Enforce capacity_total=36 and recompute reserved from confirmed registrations for both sessions.
        foreach ([$baseSession, $nextSession] as $s) {
            EventSession::query()->whereKey($s->id)->update(['capacity_total' => 36]);
            $reserved = (int) Registration::query()
                ->where('event_session_id', $s->id)
                ->where('status', 'confirmed')
                ->sum('total_count');
            EventSession::query()->whereKey($s->id)->update(['capacity_reserved' => $reserved]);
            $s->capacity_total = 36;
            $s->capacity_reserved = $reserved;
        }

        // Determine which session should be active, respecting admin status and capacity.
        // Primary: cutoff-based preference. Fallback: try the other week if primary is paused or full.
        // If both are paused, mark all hidden and return null (registration unavailable).
        $baseSession = $baseSession->fresh();
        $nextSession = $nextSession->fresh();

        $primarySession = $preferNext ? $nextSession : $baseSession;
        $fallbackSession = $preferNext ? $baseSession : $nextSession;

        $primaryHasCapacity = $primarySession->capacity_reserved < $primarySession->capacity_total;
        $fallbackHasCapacity = $fallbackSession->capacity_reserved < $fallbackSession->capacity_total;

        if ($primarySession->isOpen() && $primaryHasCapacity) {
            $activeSession = $primarySession;
        } elseif (!$preferNext && $fallbackSession->isOpen() && $fallbackHasCapacity) {
            $activeSession = $fallbackSession;
        } elseif (!$preferNext && $fallbackSession->isOpen()) {
            // Primary is full but fallback exists and is open - check if we should activate fallback anyway
            // This case handles when primary is full but fallback should still be available
            $activeSession = $fallbackSession;
        } elseif ($primarySession->isOpen() && !$primaryHasCapacity && $fallbackSession->isOpen() && $fallbackHasCapacity) {
            // Primary is full, fallback has capacity and is open
            $activeSession = $fallbackSession;
        } else {
            // Both weeks are paused by admin (or no sessions have capacity) – close all and signal unavailability.
            EventSession::query()->where('venue_id', $venueId)->update(['registration_status' => 'hidden']);
            return null;
        }

        EventSession::query()->where('venue_id', $venueId)->update(['registration_status' => 'hidden']);
        EventSession::query()->whereKey($activeSession->id)->update(['registration_status' => 'open']);

        return $activeSession->fresh();
    }

    public function step1()
    {
        $venues = Venue::query()
            ->orderBy('name')
            ->get();

        $draft = Session::get(self::DRAFT_KEY, []);
        $selectedVenueId = request('venue_id') ?? ($draft['venue_id'] ?? null);

        // Auto-select default venue on first visit
        if (!$selectedVenueId) {
            $defaultId = Venue::query()
                ->where('name', self::DEFAULT_VENUE_NAME)
                ->value('id');
            if ($defaultId) {
                $selectedVenueId = (int) $defaultId;
            }
        }

        if ($selectedVenueId) {
            $draft['venue_id'] = (int) $selectedVenueId;
            Session::put(self::DRAFT_KEY, $draft);
        }

        $sessions = collect();
        $registrationBlocked = false;
        if ($selectedVenueId) {
            $this->ensureSingleActiveSundaySession((int) $selectedVenueId);

            $now = Carbon::now(self::EVENT_TZ);
            $baseSunday = $now->copy();
            if ($baseSunday->dayOfWeek !== Carbon::SUNDAY) {
                $baseSunday = $baseSunday->next(Carbon::SUNDAY);
            }
            $baseSunday = $baseSunday->setTime(self::EVENT_HOUR, self::EVENT_MINUTE, 0);
            $nextSunday = $baseSunday->copy()->addWeek();

            $dayStart = $baseSunday->copy()->startOfDay();
            $dayEnd = $baseSunday->copy()->endOfDay();
            $nextDayStart = $nextSunday->copy()->startOfDay();
            $nextDayEnd = $nextSunday->copy()->endOfDay();

            $sessions = EventSession::query()
                ->where('venue_id', $selectedVenueId)
                ->where(function ($query) use ($dayStart, $dayEnd, $nextDayStart, $nextDayEnd) {
                    $query->whereBetween('starts_at', [$dayStart, $dayEnd])
                          ->orWhereBetween('starts_at', [$nextDayStart, $nextDayEnd]);
                })
                ->orderBy('starts_at')
                ->get();

            $activeSessions = $sessions->where('registration_status', 'open');
            if ($activeSessions->isEmpty() && $sessions->isNotEmpty()) {
                $registrationBlocked = true;
            }
        }

        return view('public.register.step1', [
            'venues' => $venues,
            'sessions' => $sessions,
            'draft' => $draft,
            'registrationBlocked' => $registrationBlocked,
        ]);
    }

    public function postStep1(Request $request)
    {
        $venueIds = Venue::query()->pluck('id')->all();

        $data = $request->validate([
            'venue_id' => ['required', Rule::in($venueIds)],
            'event_session_id' => ['required', 'integer'],
        ]);

        $session = EventSession::query()
            ->where('id', $data['event_session_id'])
            ->where('venue_id', $data['venue_id'])
            ->where('registration_status', 'open')
            ->first();

        if (!$session) {
            throw ValidationException::withMessages([
                'event_session_id' => 'Trình chiếu không hợp lệ hoặc đang tạm hoãn.',
            ]);
        }

        $remaining = $session->capacity_total - $session->capacity_reserved;
        if ($remaining <= 0) {
            throw ValidationException::withMessages([
                'event_session_id' => 'Trình chiếu này đã hết chỗ.',
            ]);
        }

        $draft = Session::get(self::DRAFT_KEY, []);
        $draft['venue_id'] = (int) $data['venue_id'];
        $draft['event_session_id'] = (int) $data['event_session_id'];

        Session::put(self::DRAFT_KEY, $draft);

        return redirect()->to('/register/step2');
    }

    public function step2()
    {
        $draft = Session::get(self::DRAFT_KEY, []);

        return view('public.register.step2', [
            'draft' => $draft,
        ]);
    }

    public function postStep2(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone_country' => ['nullable', 'string', 'max:8'],
            'phone_number' => ['nullable', 'string', 'max:32'],
            'attend_with_guest' => ['nullable', 'boolean'],
        ]);

        $phone = null;
        $country = trim((string) ($data['phone_country'] ?? ''));
        $number = preg_replace('/\D+/', '', (string) ($data['phone_number'] ?? ''));
        if ($number !== '') {
            $country = $country !== '' ? $country : '+84';
            if (!str_starts_with($country, '+')) {
                $country = '+' . $country;
            }
            $phone = $country . $number;
        }

        $draft = Session::get(self::DRAFT_KEY, []);
        $draft = array_merge($draft, [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $phone,
            'attend_with_guest' => (bool) ($data['attend_with_guest'] ?? false),
        ]);
        Session::put(self::DRAFT_KEY, $draft);

        return redirect()->to('/register/step3');
    }

    public function step3()
    {
        $draft = Session::get(self::DRAFT_KEY, []);

        return view('public.register.step3', [
            'draft' => $draft,
        ]);
    }

    public function postStep3(Request $request)
    {
        $data = $request->validate([
            'adult_count' => ['required', 'integer', 'min:0', 'max:999'],
            'ntl_count' => ['required', 'integer', 'min:0', 'max:999'],
            'ntl_new_count' => ['required', 'integer', 'min:0', 'max:999'],
            'child_count' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $draft = Session::get(self::DRAFT_KEY, []);
        $attendWithGuest = (bool) ($draft['attend_with_guest'] ?? false);

        $guestTotal =
            (int) $data['adult_count'] +
            (int) $data['ntl_count'] +
            (int) $data['ntl_new_count'] +
            (int) $data['child_count'];

        if ($guestTotal <= 0) {
            throw ValidationException::withMessages([
                'adult_count' => 'Tổng số khách phải lớn hơn 0.',
            ]);
        }

        if ($attendWithGuest && $guestTotal < 1) {
            throw ValidationException::withMessages([
                'adult_count' => 'Vui lòng chọn ít nhất 1 khách đi cùng.',
            ]);
        }

        $total = $guestTotal + ($attendWithGuest ? 1 : 0);

        $draft = array_merge($draft, $data);
        $draft['total_count'] = $total;
        Session::put(self::DRAFT_KEY, $draft);

        return redirect()->to('/register/step4');
    }

    public function step4()
    {
        $draft = Session::get(self::DRAFT_KEY, []);
        $session = null;
        $venue = null;

        if (!empty($draft['event_session_id'])) {
            $session = EventSession::query()->with('venue')->find($draft['event_session_id']);
            $venue = $session?->venue;
        }

        return view('public.register.step4', [
            'draft' => $draft,
            'venue' => $venue,
            'session' => $session,
        ]);
    }

    public function submit()
    {
        $draft = Session::get(self::DRAFT_KEY, []);
        $required = ['venue_id', 'event_session_id', 'full_name', 'email', 'adult_count', 'ntl_count', 'ntl_new_count', 'child_count', 'total_count'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $draft)) {
                return redirect()->to('/register');
            }
        }

        $registration = DB::transaction(function () use ($draft) {
            $session = EventSession::query()
                ->where('id', $draft['event_session_id'])
                ->with('venue')
                ->lockForUpdate()
                ->firstOrFail();

            $total = (int) $draft['total_count'];

            if (!$session->isOpen()) {
                throw ValidationException::withMessages([
                    'event_session_id' => 'Trình chiếu đã đóng hoặc đang tạm hoãn.',
                ]);
            }

            if ($session->capacity_reserved + $total > $session->capacity_total) {
                throw ValidationException::withMessages([
                    'total_count' => 'Không đủ chỗ cho số lượng khách bạn chọn.',
                ]);
            }

            $newReserved = $session->capacity_reserved + $total;
            EventSession::query()
                ->whereKey($session->id)
                ->update(['capacity_reserved' => $newReserved]);
            $session->capacity_reserved = $newReserved;

            return Registration::query()->create([
                'event_session_id' => $session->id,
                'full_name' => $draft['full_name'],
                'email' => $draft['email'],
                'phone' => $draft['phone'] ?? null,
                'adult_count' => (int) $draft['adult_count'],
                'ntl_count' => (int) $draft['ntl_count'],
                'ntl_new_count' => (int) $draft['ntl_new_count'],
                'child_count' => (int) $draft['child_count'],
                'total_count' => (int) $draft['total_count'],
                'attend_with_guest' => (bool) ($draft['attend_with_guest'] ?? false),
            ]);
        });

        if (!$registration) {
            return redirect()->back()->withInput();
        }

        $registration->load('eventSession.venue');
        Mail::to($registration->email)->send(new RegistrationConfirmed($registration));

        Session::forget(self::DRAFT_KEY);

        return redirect()->to('/register/success/' . $registration->id);
    }

    public function success(int $id)
    {
        $registration = Registration::query()
            ->with('eventSession.venue')
            ->findOrFail($id);

        return view('public.register.success', [
            'registration' => $registration,
        ]);
    }
}
