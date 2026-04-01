<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EventSession;
use App\Models\Registration;
use App\Models\Venue;
use App\Mail\RegistrationConfirmed;
use App\Services\SessionGeneratorService;
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

    // -------------------------------------------------------------------------
    // Ensures event sessions exist for the current ISO week (Mon–Sun) and the
    // next ISO week using the SessionGeneratorService with template-based
    // generation. No hardcoded fallback logic.
    // -------------------------------------------------------------------------
    private function ensureUpcomingWeekSessions(int $venueId): void
    {
        app(SessionGeneratorService::class)->ensureSessionsForVenue($venueId);
    }

    // =========================================================================
    // Step 1 — pick a venue + session date
    // =========================================================================
    public function step1()
    {
        $venues = Venue::query()
            ->orderBy('name')
            ->get();

        $draft = Session::get(self::DRAFT_KEY, []);
        $selectedVenueId = request('venue_id') ?? ($draft['venue_id'] ?? null);

        // Auto-select the default venue on first visit
        if (!$selectedVenueId) {
            $defaultId = Venue::query()
                ->where('name', self::DEFAULT_VENUE_NAME)
                ->value('id');
            if ($defaultId) {
                $selectedVenueId = (int) $defaultId;
            } else {
                // Fallback: select first venue
                $selectedVenueId = $venues->first()?->id;
            }
        }

        if ($selectedVenueId) {
            $draft['venue_id'] = (int) $selectedVenueId;
            Session::put(self::DRAFT_KEY, $draft);
        }

        $sessions = collect();

        if ($selectedVenueId) {
            // Guarantee sessions exist for this week + next week
            $this->ensureUpcomingWeekSessions((int) $selectedVenueId);

            $now = Carbon::now(self::EVENT_TZ);

            $thisWeekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
            $thisWeekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY);
            $nextWeekStart = $thisWeekStart->copy()->addWeek();
            $nextWeekEnd = $thisWeekEnd->copy()->addWeek();

            // Show all sessions in the two-week window that:
            //   1. Have not started yet (starts_at > now)
            //   2. Are not hidden by the admin
            $sessions = EventSession::query()
                ->where('venue_id', $selectedVenueId)
                ->where('registration_status', '!=', 'hidden')
                ->where('starts_at', '>', $now)
                ->whereBetween('starts_at', [$thisWeekStart, $nextWeekEnd])
                ->orderBy('starts_at')
                ->get();

            // Auto-select the first valid session if none is selected or selected is invalid
            $selectedSessionId = $draft['event_session_id'] ?? null;
            $selectedSession = $selectedSessionId ? $sessions->firstWhere('id', $selectedSessionId) : null;

            if (!$selectedSession || $selectedSession->isPaused() || ($selectedSession->capacity_total - $selectedSession->capacity_reserved) <= 0) {
                $firstValidSession = $sessions->first(function ($s) {
                    return !$s->isPaused() && ($s->capacity_total - $s->capacity_reserved) > 0;
                });

                if ($firstValidSession) {
                    $draft['event_session_id'] = $firstValidSession->id;
                    Session::put(self::DRAFT_KEY, $draft);
                }
            }
        }

        return view('public.register.step1', [
            'venues' => $venues,
            'sessions' => $sessions,
            'draft' => $draft,
        ]);
    }

    // =========================================================================
    // Step 1 POST — validate & persist chosen session
    // =========================================================================
    public function postStep1(Request $request)
    {
        $venueIds = Venue::query()->pluck('id')->all();

        $data = $request->validate([
            'venue_id' => ['required', Rule::in($venueIds)],
            'event_session_id' => ['required', 'integer'],
        ]);

        $now = Carbon::now(self::EVENT_TZ);

        // Session must belong to the venue, not be hidden, and not have started yet
        $session = EventSession::query()
            ->where('id', $data['event_session_id'])
            ->where('venue_id', $data['venue_id'])
            ->where('registration_status', '!=', 'hidden')
            ->where('starts_at', '>', $now)
            ->first();

        if (!$session) {
            throw ValidationException::withMessages([
                'event_session_id' => 'Trình chiếu không hợp lệ, đã đóng, hoặc đang tạm hoãn.',
            ]);
        }

        // Paused (postponed) sessions are visible but cannot be registered
        if ($session->isPaused()) {
            throw ValidationException::withMessages([
                'event_session_id' => 'Trình chiếu này đang tạm hoãn, không thể đăng ký.',
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

    // =========================================================================
    // Steps 2, 3, 4 — collect attendee information
    // =========================================================================
    public function step2()
    {
        $draft = Session::get(self::DRAFT_KEY, []);

        return view('public.register.step2', [
            'draft' => $draft,
        ]);
    }

    public function postStep2(Request $request)
    {
        $data = $request->validate(
            [
                'full_name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone_country' => ['required', 'string', 'max:8'],
                'phone_number' => ['required', 'string', 'regex:/^[0-9]{9,11}$/'],
                'attend_with_guest' => ['nullable', 'boolean'],
            ],
            [
                'phone_number.regex' => 'Số điện thoại không hợp lệ.',
            ]
        );

        $phone = null;
        $country = trim((string) ($data['phone_country'] ?? ''));
        $number = preg_replace('/\D+/', '', (string) ($data['phone_number'] ?? ''));
        $number = ltrim($number, '0');
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
                'adult_count' => 'Vui lòng nhập số lượng khách!',
            ]);
        }

        if ($attendWithGuest && $guestTotal < 1) {
            throw ValidationException::withMessages([
                'adult_count' => 'Vui lòng chọn ít nhất 1 khách đi cùng.',
            ]);
        }

        // $total = $guestTotal + ($attendWithGuest ? 1 : 0);

        $draft = array_merge($draft, $data);
        $draft['total_count'] = $guestTotal;
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

    // =========================================================================
    // Final submission
    // =========================================================================
    public function submit()
    {
        $draft = Session::get(self::DRAFT_KEY, []);
        $required = ['venue_id', 'event_session_id', 'full_name', 'phone', 'adult_count', 'ntl_count', 'ntl_new_count', 'child_count', 'total_count'];

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
                'email' => $draft['email'] ?? null,
                'phone' => $draft['phone'] ?? null,
                'adult_count' => (int) $draft['adult_count'],
                'ntl_count' => (int) $draft['ntl_count'],
                'ntl_new_count' => (int) $draft['ntl_new_count'],
                'child_count' => (int) $draft['child_count'],
                'total_count' => (int) $draft['total_count'],
                'attend_with_guest' => (bool) ($draft['attend_with_guest'] ?? false),
                'status' => 'pending',
            ]);
        });

        if (!$registration) {
            return redirect()->back()->withInput();
        }

        $registration->load('eventSession.venue');
        if ($registration->email) {
            Mail::to($registration->email)->send(new RegistrationConfirmed($registration));
        }

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
